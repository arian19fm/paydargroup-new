<?php

namespace Database\Factories;

use App\Models\TeamGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TeamGroup> */
class TeamGroupFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => $this->faker->unique()->words(2, true), 'sort_order' => 0, 'is_active' => true];
    }
}
