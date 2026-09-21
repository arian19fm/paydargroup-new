<?php

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Redirect> */
class RedirectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'source_path' => '/old-'.$this->faker->unique()->slug(2),
            'destination_url' => '/new-'.$this->faker->unique()->slug(2),
            'http_status' => 301,
            'is_active' => true,
        ];
    }
}
