<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    public function definition(): array
    {
        return ['filename' => 'photos/'.fake()->uuid().'.jpg', 'published' => false];
    }
}
