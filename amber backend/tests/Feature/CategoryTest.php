<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories()
    {
        $user = User::factory()->create();
        Category::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_create_category()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/categories', ['name' => 'Test Category']);

        $response->assertStatus(201)
            ->assertJson(['name' => 'Test Category']);
    }

    public function test_can_show_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $category->id]);
    }

    public function test_can_update_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/categories/{$category->id}", ['name' => 'Updated Category']);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Updated Category']);
    }

    public function test_can_delete_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200);
    }
}
