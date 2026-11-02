<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoLocationFactory extends Factory
{
    public function definition(): array
    {
        return ['route_name' => fake()->unique()->slug(2), 'name' => fake()->words(3, true)];
    }
}
