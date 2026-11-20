<?php

namespace Database\Factories;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Rental> */
class RentalFactory extends Factory
{
    public function definition(): array
    {
        $arrival = today()->addDays(fake()->numberBetween(1, 180));

        return [
            'user_id' => User::factory(),
            'guest_name' => fn (array $attributes): string => User::findOrFail($attributes['user_id'])->fullName(),
            'arrival' => $arrival->toDateString(),
            'departure' => $arrival->copy()->addWeek()->toDateString(),
            'type' => 'rental',
            'amount' => 700,
        ];
    }
}
