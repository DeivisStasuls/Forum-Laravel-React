<?php

namespace Database\Factories;

use App\Models\PrivateGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrivateGroupFactory extends Factory
{
    protected $model = PrivateGroup::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'created_by' => User::factory(),
        ];
    }
}