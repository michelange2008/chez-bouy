<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    public function definition(): array
    {
        return ['filename' => 'file/'.fake()->uuid().'.pdf', 'title' => fake()->sentence(3), 'description' => null, 'locale' => 'fr'];
    }
}
