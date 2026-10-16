<?php
namespace App\Interfaces;
use App\DTOs\SearchQueryDTO;
use Illuminate\Support\Collection; 
interface SearchRepositoryInterface
{
    public function bm25Search(SearchQueryDTO $searchQueryDTO): Collection;
    public function semanticSearch(SearchQueryDTO $searchQueryDTO, array $embedding): Collection;
}   