<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\SearchQueryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Services\HybridSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SearchController extends Controller
{
    public function __construct(
        private HybridSearchService $searchService,
    ) {}

    public function search(SearchRequest $request): JsonResponse
    {
        $dto = new SearchQueryDTO(
            query:   $request->validated('query'),
            court:   $request->validated('court'),
            year:    $request->validated('year'),
            subject: $request->validated('subject'),
            page:    $request->validated('page', 1),
        );

        $results = $this->searchService->search($dto);

        return response()->json([
            'success' => true,
            'message' => 'Search results retrieved successfully.',
            'query'   => $dto->query,
            'total'   => $results->count(),
            'data'    => $results,
        ],Response::HTTP_OK);
    }
}