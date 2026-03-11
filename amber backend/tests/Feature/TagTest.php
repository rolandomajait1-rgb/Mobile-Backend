<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_tags()
    {
        $user = User::factory()->create();
        Tag::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/tags');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_create_tag()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tags', ['name' => 'Test Tag']);

        $response->assertStatus(201)
            ->assertJson(['name' => 'Test Tag']);
    }

    public function test_can_show_tag()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/tags/{$tag->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $tag->id]);
    }

    public function test_can_update_tag()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/tags/{$tag->id}", ['name' => 'Updated Tag']);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Updated Tag']);
    }

    public function test_can_delete_tag()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/tags/{$tag->id}");

        $response->assertStatus(200);
    }
}
