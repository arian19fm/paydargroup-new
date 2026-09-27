<?php

namespace Database\Factories;

use App\Models\TeamGroup;
use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TeamMember> */
class TeamMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_group_id' => TeamGroup::factory(),
            'name' => $this->faker->name(),
            'role' => $this->faker->jobTitle(),
            'linkedin_url' => null,
            'photo_media_id' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
