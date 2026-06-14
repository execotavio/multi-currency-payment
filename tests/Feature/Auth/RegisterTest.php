<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'BR',
            'currency' => 'BRL',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'token_type', 'user']);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'employee',
            'country' => 'BR',
            'currency' => 'BRL',
        ]);
    }

    public function test_register_rejects_role_in_payload(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'BR',
            'currency' => 'BRL',
            'role' => 'finance',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_register_requires_country_and_currency(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country', 'currency']);
    }
}
