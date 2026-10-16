<?php

namespace App\Repositories;

use App\DTOs\SearchQueryDTO;
use App\DTOs\SearchResultDTO;
use App\Interfaces\SearchRepositoryInterface;
use App\Models\Judgment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SearchRepository implements SearchRepositoryInterface
{
    public function bm25Search(SearchQueryDTO $dto): Collection
    {
        $query = DB::select("
            SELECT
                j.id, j.citation, j.title, j.year, j.subject, j.source_url,
                c.short_name as court,
                LEFT(j.full_text, 300) as snippet,
                ts_rank(j.search_vector, plainto_tsquery('english', ?)) as score
            FROM judgments j
            JOIN courts c ON c.id = j.court_id
            WHERE j.search_vector @@ plainto_tsquery('english', ?)
            " . $this->filters($dto) . "
            ORDER BY score DESC
            LIMIT 20
        ", [$dto->query, $dto->query]);

        return collect($query)->map(fn($row) => new SearchResultDTO(
            id:       $row->id,
            citation: $row->citation,
            title:    $row->title,
            court:    $row->court,
            year:     $row->year,
            subject:  $row->subject,
            snippet:  $row->snippet,
            score:    (float) $row->score,
        ));
    }

    public function semanticSearch(SearchQueryDTO $dto, array $embedding): Collection
    {
        $vector = '[' . implode(',', $embedding) . ']';

        $query = DB::select("
            SELECT
                j.id, j.citation, j.title, j.year, j.subject,
                c.short_name as court,
                LEFT(j.full_text, 300) as snippet,
                1 - (j.embedding <=> ?) as score
            FROM judgments j
            JOIN courts c ON c.id = j.court_id
            WHERE j.embedding IS NOT NULL
            " . $this->filters($dto) . "
            ORDER BY j.embedding <=> ?
            LIMIT 20
        ", [$vector, $vector]);

        return collect($query)->map(fn($row) => new SearchResultDTO(
            id:       $row->id,
            citation: $row->citation,
            title:    $row->title,
            court:    $row->court,
            year:     $row->year,
            subject:  $row->subject,
            snippet:  $row->snippet,
            score:    (float) $row->score,
        ));
    }

    private function filters(SearchQueryDTO $dto): string
    {
        $clauses = [];

        if ($dto->court)   $clauses[] = "AND c.short_name = '{$dto->court}'";
        if ($dto->year)    $clauses[] = "AND j.year = {$dto->year}";
        if ($dto->subject) $clauses[] = "AND j.subject ILIKE '%{$dto->subject}%'";

        return implode(' ', $clauses);
    }
}