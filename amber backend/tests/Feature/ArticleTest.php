<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_articles()
    {
        $user = User::factory()->create();
        Article::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_create_article()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/articles', [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'category_id' => $category->id,
            ]);

        $response->assertStatus(201)
            ->assertJson(['title' => 'Test Article']);
    }

    public function test_can_show_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $article->id]);
    }

    public function test_can_update_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/articles/{$article->id}", ['title' => 'Updated Title']);

        $response->assertStatus(200)
            ->assertJson(['title' => 'Updated Title']);
    }

    public function test_can_delete_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(200);
    }

    public function test_can_like_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/articles/{$article->id}/like");

        $response->assertStatus(200);
    }

    public function test_can_unlike_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/articles/{$article->id}/like");

        $response->assertStatus(200);
    }

    public function test_can_share_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/articles/{$article->id}/share");

        $response->assertStatus(200);
    }
}
