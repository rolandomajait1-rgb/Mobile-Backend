<?php

namespace Tests\Feature;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_team_members()
    {
        $user = User::factory()->create();
        TeamMember::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/team-members');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_create_team_member()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/team-members', [
                'name' => 'John Doe',
                'position' => 'Editor',
                'bio' => 'Test bio',
            ]);

        $response->assertStatus(201)
            ->assertJson(['position' => 'Editor']);
    }

    public function test_can_show_team_member()
    {
        $user = User::factory()->create();
        $teamMember = TeamMember::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/team-members/{$teamMember->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $teamMember->id]);
    }

    public function test_can_update_team_member()
    {
        $user = User::factory()->create();
        $teamMember = TeamMember::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/team-members/{$teamMember->id}", ['position' => 'Senior Editor']);

        $response->assertStatus(200)
            ->assertJson(['position' => 'Senior Editor']);
    }

    public function test_can_delete_team_member()
    {
        $user = User::factory()->create();
        $teamMember = TeamMember::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/team-members/{$teamMember->id}");

        $response->assertStatus(200);
    }
}
