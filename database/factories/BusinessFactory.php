<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Business> */
class BusinessFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->unique()->company();

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'tagline' => $this->faker->sentence(),
            'features' => $this->faker->words(4),
            'accent' => $this->faker->randomElement(Business::ACCENTS),
            'image_media_id' => null,
            'excerpt' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'website_url' => null,
            'status' => ContentStatus::Draft,
            'published_at' => null,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => ContentStatus::Published, 'published_at' => now()->subMinute()]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => ['status' => ContentStatus::Published, 'published_at' => now()->addDay()]);
    }
}
