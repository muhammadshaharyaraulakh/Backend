<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourtFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'            => fake()->randomElement([
                'Supreme Court',
                'Lahore High Court',
                'Sindh High Court',
                'Peshawar High Court',
                'Islamabad High Court',
                'Balochistan High Court',
                'Federal Shariat Court',
            ]),
            'short_name'      => fake()->randomElement(['SC', 'LHC', 'SHC', 'PHC', 'IHC', 'BHC', 'FSC']),
            'hierarchy_level' => fake()->numberBetween(1, 3),
        ];
    }
}