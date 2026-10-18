<?php

namespace App\Console\Commands;

use App\Services\IngestionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('judgments:ingest {--limit=100 : How many Judgements need to be fetched}')]
#[Description('Fetch judgments from pakistancaselaw.com and store in database')]
class IngestJudgments extends Command
{
    public function handle(IngestionService $ingestionService): int
    {
        $limit = (int) $this->option('limit');

        $this->info("Ingestion shuru ho rahi hai — limit: {$limit}");

        $log = $ingestionService->ingest($limit);

        if ($log->status === 'failed') {
            $this->error("Ingestion fail ho gayi: {$log->error_message}");
            return Command::FAILURE;
        }

        $this->table(
            ['Fetched', 'Inserted', 'Skipped', 'Failed', 'Time'],
            [[
                $log->fetched,
                $log->inserted,
                $log->skipped,
                $log->failed,
                $log->started_at->diffForHumans($log->completed_at, true),
            ]]
        );

        $this->info('Ingestion complete!');

        return Command::SUCCESS;
    }
}