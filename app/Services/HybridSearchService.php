<?php

namespace App\Services;

use App\DTOs\SearchQueryDTO;
use App\DTOs\SearchResultDTO;
use App\Interfaces\SearchRepositoryInterface;
use Illuminate\Support\Collection;

class HybridSearchService
{
    public function __construct(
        private SearchRepositoryInterface $repository,
        private EmbeddingService $embeddingService,
    ) {}

    public function search(SearchQueryDTO $dto): Collection
    {
        // Step 1: Coverting Query to Vector Embedding
        $embedding = $this->embeddingService->embed($dto->query);

        // Step 2: Parallel searches
        $bm25Results     = $this->repository->bm25Search($dto);
        $semanticResults = $this->repository->semanticSearch($dto, $embedding);

        // Step 3: Merge with RRF
        return $this->reciprocalRankFusion($bm25Results, $semanticResults);
    }

    private function reciprocalRankFusion(
        Collection $bm25,
        Collection $semantic,
        int $k = 60
    ): Collection {
        $scores = [];

        // Calculating BM25 ranks scores
        foreach ($bm25->values() as $rank => $result) {
            $scores[$result->id] = ($scores[$result->id] ?? 0) + 1 / ($k + $rank + 1);
        }

        // Adding Semantic ranks scores
        foreach ($semantic->values() as $rank => $result) {
            $scores[$result->id] = ($scores[$result->id] ?? 0) + 1 / ($k + $rank + 1);
        }


        arsort($scores);

        $allResults = $bm25->concat($semantic)->keyBy('id');

        return collect(array_keys($scores))
            ->map(fn($id) => $allResults->get($id))
            ->filter()
            ->values();
    }
}