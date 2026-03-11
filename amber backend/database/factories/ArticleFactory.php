<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(3, true),
            'featured_image' => fake()->imageUrl(),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'category_id' => \App\Models\Category::factory(),
            'author_id' => \App\Models\User::factory(),
            'author_name' => fake()->name(),
        ];
    }
}
