<?php
namespace App\DTOs;
readonly class SearchQueryDTO
{
    public function __construct(
        public string $query,
        public ?string $court = null,
        public ?int $year = null,
        public ?string $subject = null,
        public ?int $page = 1
    ) {}
}
?>