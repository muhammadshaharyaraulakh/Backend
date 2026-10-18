<?php

namespace App\Interfaces;

use App\DTOs\JudgmentDTO;

interface JudgmentRepositoryInterface
{
    public function store(JudgmentDTO $dto, array $embedding): void;
    public function existsByCitation(string $citation): bool;
    public function getCourtIdByShortName(string $shortName): ?int;
}