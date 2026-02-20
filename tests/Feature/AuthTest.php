<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/register', [
                'username' => 'johndoe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['username' => 'johndoe', 'email' => 'john@example.com']);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com', 'username' => 'johndoe']);
    }

    public function test_registration_fails_with_invalid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'username' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'email', 'password']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/register', [
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_duplicate_username(): void
    {
        User::factory()->create(['username' => 'johndoe']);

        $response = $this->postJson('/api/register', [
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/login', [
                'email' => 'john@example.com',
                'password' => 'password',
            ]);

        $response->assertOk()
            ->assertJsonFragment(['email' => 'john@example.com']);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/login', [
                'email' => 'john@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['message' => 'Invalid credentials.']);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeader('Origin', 'http://localhost')
            ->postJson('/api/logout');

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Logged out.']);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_user_endpoint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }
}
