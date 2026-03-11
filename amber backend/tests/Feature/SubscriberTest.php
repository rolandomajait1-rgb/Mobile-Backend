<?php

namespace Tests\Feature;

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriberTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_subscribe()
    {
        $response = $this->postJson('/api/subscribers/subscribe', [
            'email' => 'subscriber@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Successfully subscribed!']);
    }

    public function test_can_unsubscribe()
    {
        $subscriber = Subscriber::factory()->create(['email' => 'subscriber@example.com']);

        $response = $this->postJson('/api/subscribers/unsubscribe', [
            'email' => 'subscriber@example.com',
        ]);

        $response->assertStatus(200);
    }

    public function test_can_list_subscribers()
    {
        $user = User::factory()->create();
        Subscriber::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/subscribers');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_show_subscriber()
    {
        $user = User::factory()->create();
        $subscriber = Subscriber::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/subscribers/{$subscriber->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $subscriber->id]);
    }

    public function test_can_delete_subscriber()
    {
        $user = User::factory()->create();
        $subscriber = Subscriber::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/subscribers/{$subscriber->id}");

        $response->assertStatus(200);
    }
}
