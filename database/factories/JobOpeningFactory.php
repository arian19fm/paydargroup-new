<?php

namespace Database\Factories;

use App\Models\JobOpening;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobOpening> */
class JobOpeningFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle(),
            'category' => $this->faker->word(),
            'tone' => $this->faker->randomElement(array_keys(JobOpening::TONES)),
            'description' => $this->faker->sentence(),
            'employment_type' => 'تمام‌وقت',
            'location' => 'تهران / حضوری',
            'apply_url' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
