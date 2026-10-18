<?php

namespace App\Repositories;

use App\DTOs\JudgmentDTO;
use App\Interfaces\JudgmentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class JudgmentRepository implements JudgmentRepositoryInterface
{
    public function store(JudgmentDTO $dto, array $embedding): void
    {
        $vector = '[' . implode(',', $embedding) . ']';

        DB::statement("
            INSERT INTO judgments
                (citation, title, court_id, year, subject, judges, full_text, source_url, embedding, search_vector, ingested_at, created_at, updated_at)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?::vector, to_tsvector('english', ?), NOW(), NOW(), NOW())
        ", [
            $dto->citation,
            $dto->title,
            $this->getCourtIdByShortName($dto->courtShortName),
            $dto->year,
            $dto->subject,
            json_encode($dto->judges),
            $dto->fullText,
            $dto->sourceUrl,
            $vector,
            $dto->fullText,
        ]);
    }

    public function existsByCitation(string $citation): bool
    {
        return DB::table('judgments')
            ->where('citation', $citation)
            ->exists();
    }

    public function getCourtIdByShortName(string $shortName): ?int
    {
        $court = DB::table('courts')
            ->where('short_name', $shortName)
            ->first();

        return $court?->id;
    }
}