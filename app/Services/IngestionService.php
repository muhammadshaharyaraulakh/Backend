<?php

namespace App\Services;

use App\DTOs\JudgmentDTO;
use App\Interfaces\JudgmentRepositoryInterface;
use App\Models\IngestionLog;
use Illuminate\Support\Facades\Log;

class IngestionService
{
    public function __construct(
        private readonly JudgmentRepositoryInterface $repository,
        private readonly EmbeddingService $embeddingService,
        private readonly McpService $mcpService,
    ) {}

    public function ingest(int $limit = 50): IngestionLog
    {
        $log = IngestionLog::create([
            'status'     => 'running',
            'started_at' => now(),
        ]);

        try {
            $this->mcpService->initialize();

            $cases = $this->mcpService->searchJudgments($limit);
            $log->update(['fetched' => count($cases)]);

            foreach ($cases as $case) {
                $this->processCase($case, $log);
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

    private function processCase(array $case, IngestionLog $log): void
    {
        if ($this->repository->existsByCitation($case['citation'])) {
            $log->increment('skipped');
            return;
        }

        try {
            $fullCase = $this->mcpService->getFullCase($case['id']);

            if (empty($fullCase['body'])) {
                $log->increment('skipped');
                return;
            }

            $dto = new JudgmentDTO(
                citation:       $fullCase['citation'],
                title:          $fullCase['title'],
                courtShortName: $this->mapCourt($fullCase['court'] ?? ''),
                year:           (int) $fullCase['year'],
                fullText:       $fullCase['body'],
                subject:        $fullCase['subject'] ?? null,
                judges:         isset($fullCase['author']) ? [$fullCase['author']] : null,
                sourceUrl:      $fullCase['url'] ?? null,
            );

            $embedding = $this->embeddingService->embed(
                substr($dto->fullText, 0, 2000)
            );

            $this->repository->store($dto, $embedding);
            $log->increment('inserted');

        } catch (\Throwable $e) {
            $log->increment('failed');
            Log::warning('Case failed', [
                'id'    => $case['id'] ?? '?',
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function mapCourt(string $name): string
    {
        return match(true) {
            str_contains($name, 'Supreme')     => 'SC',
            str_contains($name, 'Lahore')      => 'LHC',
            str_contains($name, 'Sindh')       => 'SHC',
            str_contains($name, 'Peshawar')    => 'PHC',
            str_contains($name, 'Islamabad')   => 'IHC',
            str_contains($name, 'Balochistan') => 'BHC',
            str_contains($name, 'Shariat')     => 'FSC',
            default                            => 'SC',
        };
    }
}