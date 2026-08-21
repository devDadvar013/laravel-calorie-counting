<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_token_and_public_user(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'email' => 'mahdi@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'name']])
            ->assertJsonPath('user.email', 'mahdi@example.com')
            ->assertJsonPath('user.name', 'Mahdi');

        $this->assertNotNull($response->json('token'));
    }

    public function test_register_accepts_optional_name(): void
    {
        $this->postJson('/api/auth/register', [
            'email' => 'ali@example.com',
            'password' => 'secret123',
            'name' => 'علی رضایی',
        ])->assertStatus(201)
            ->assertJsonPath('user.name', 'علی رضایی');
    }

    public function test_register_rejects_duplicate_email_with_409(): void
    {
        User::factory()->create(['email' => 'dupe@example.com']);

        $this->postJson('/api/auth/register', [
            'email' => 'dupe@example.com',
            'password' => 'secret123',
        ])->assertStatus(409)
            ->assertJsonPath('message', 'این ایمیل قبلاً ثبت شده است');
    }

    public function test_register_rejects_short_password(): void
    {
        $this->postJson('/api/auth/register', [
            'email' => 'x@example.com',
            'password' => '123',
        ])->assertStatus(422);
    }

    public function test_login_returns_token_and_user(): void
    {
        User::factory()->create([
            'email' => 'demo@example.com',
            'password' => 'demo1234',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'demo@example.com',
            'password' => 'demo1234',
        ])->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'name']])
            ->assertJsonPath('user.email', 'demo@example.com');
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        User::factory()->create([
            'email' => 'demo@example.com',
            'password' => 'demo1234',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'demo@example.com',
            'password' => 'wrongpass',
        ])->assertStatus(401)
            ->assertJsonPath('message', 'ایمیل یا رمز عبور اشتباه است');
    }

    public function test_login_with_unknown_email_returns_401(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'ghost@example.com',
            'password' => 'secret123',
        ])->assertStatus(401);
    }

    public function test_me_returns_current_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ]);
    }

    public function test_me_requires_token(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }
}
