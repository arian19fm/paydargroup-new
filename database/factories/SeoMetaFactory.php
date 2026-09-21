<?php

namespace Database\Factories;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SeoMeta> */
class SeoMetaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'robots_index' => true,
            'robots_follow' => true,
        ];
    }
}
