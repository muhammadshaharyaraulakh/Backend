<?php

namespace App\Services;

use App\DTOs\JudgmentDTO;
use App\Interfaces\JudgmentRepositoryInterface;
use App\Models\IngestionLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IngestionService
{
    public function __construct(
        private readonly JudgmentRepositoryInterface $repository,
        private readonly EmbeddingService $embeddingService,
    ) {}

    public function ingest(int $limit = 100): IngestionLog
    {
        $log = IngestionLog::create([
            'status'     => 'running',
            'started_at' => now(),
        ]);

        try {
            $judgments = $this->fetchFromMcp($limit);
            $log->update(['fetched' => count($judgments)]);

            foreach ($judgments as $judgment) {
                $this->processJudgment($judgment, $log);
            }

            $log->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);

        } catch (\Throwable $e) {
            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);

            Log::error('Ingestion failed', ['error' => $e->getMessage()]);
        }

        return $log;
    }

    private function fetchFromMcp(int $limit): array
    {
        $response = Http::post('https://pakistancaselaw.com/mcp', [
            'jsonrpc' => '2.0',
            'method'  => 'tools/call',
            'id'      => 1,
            'params'  => [
                'name'      => 'search_judgments',
                'arguments' => ['limit' => $limit],
            ],
        ]);

        return $response->json('result.judgments', []);
    }

    private function processJudgment(array $data, IngestionLog $log): void
    {
        if ($this->repository->existsByCitation($data['citation'])) {
            $log->increment('skipped');
            return;
        }

        try {
            $dto = new JudgmentDTO(
                citation:       $data['citation'],
                title:          $data['title'],
                courtShortName: $data['court'],
                year:           $data['year'],
                fullText:       $data['full_text'],
                subject:        $data['subject'] ?? null,
                judges:         $data['judges']  ?? null,
                sourceUrl:      $data['source_url'] ?? null,
            );

            $embedding = $this->embeddingService->embed(
                substr($dto->fullText, 0, 2000)
            );

            $this->repository->store($dto, $embedding);
            $log->increment('inserted');

        } catch (\Throwable $e) {
            $log->increment('failed');
            Log::warning('Judgment failed', [
                'citation' => $data['citation'] ?? 'unknown',
                'error'    => $e->getMessage(),
            ]);
        }
    }
}