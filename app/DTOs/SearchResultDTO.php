<?php

namespace App\DTOs;

readonly class SearchResultDTO
{
    public function __construct(
        public int    $id,
        public string $citation,
        public string $title,
        public string $court,
        public int    $year,
        public ?string $subject,
        public string  $snippet,
        public float   $score,
    ) {}
}