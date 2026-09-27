<?php

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobApplication> */
class JobApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_opening_id' => JobOpening::factory(),
            'job_title' => $this->faker->jobTitle(),
            'phone' => '0912'.$this->faker->numerify('#######'),
            'resume_path' => 'resumes/2026/09/'.$this->faker->uuid().'.pdf',
            'resume_name' => 'resume.pdf',
            'resume_mime' => 'application/pdf',
            'resume_size' => 12345,
            'seen_at' => null,
        ];
    }

    public function seen(): static
    {
        return $this->state(fn () => ['seen_at' => now()]);
    }
}
