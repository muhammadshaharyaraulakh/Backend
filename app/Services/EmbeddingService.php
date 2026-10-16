<?php

namespace App\Services;

use Illuminate\Support\Facades\AI;

class EmbeddingService
{
    public function embed(string $text): array
    {
        $response = AI::embed(
            input: $text,
            options: [
                'output_dimensionality' => 1536,
            ]
        );

        return $response->embeddings[0]->values;
    }
}