<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_requires_authentication(): void
    {
        $this->getJson('/api/goal')->assertStatus(401);
        $this->putJson('/api/goal', ['goal' => 1500])->assertStatus(401);
    }

    public function test_returns_default_goal(): void
    {
        $user = User::factory()->create(['goal' => 2000]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/goal')
            ->assertOk()
            ->assertJson(['goal' => 2000]);
    }

    public function test_updates_goal(): void
    {
        $user = User::factory()->create(['goal' => 2000]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/goal', ['goal' => 1800])
            ->assertOk()
            ->assertJson(['goal' => 1800]);

        $this->assertSame(1800, $user->fresh()->goal);
    }

    public function test_rejects_invalid_goal(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/goal', ['goal' => -10])
            ->assertStatus(422);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/goal', ['goal' => 'abc'])
            ->assertStatus(422);
    }
}
