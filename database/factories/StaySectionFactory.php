<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StaySectionFactory extends Factory
{
    public function definition(): array
    {
        return ['page' => 'arrivee', 'position' => 1, 'published' => false, 'translations' => ['fr' => ['title' => fake()->sentence(3), 'body' => fake()->paragraph()]]];
    }
}
