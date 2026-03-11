<?php

namespace Database\Factories;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'user_id' => User::factory(),
            'position' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'image' => fake()->imageUrl(),
            'social_links' => [
                'twitter' => fake()->url(),
                'linkedin' => fake()->url(),
            ],
            'order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}
