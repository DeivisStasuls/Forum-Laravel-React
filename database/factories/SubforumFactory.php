<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubforumFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
        ];
    }
}