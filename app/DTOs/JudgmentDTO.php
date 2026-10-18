<?php

namespace App\DTOs;

readonly class JudgmentDTO
{
    public function __construct(
        public string  $citation,
        public string  $title,
        public string  $courtShortName,
        public int     $year,
        public string  $fullText,
        public ?string $subject    = null,
        public ?array  $judges     = null,
        public ?string $sourceUrl  = null,
    ) {}
}