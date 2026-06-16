<?php

namespace Tests\Feature\Auth;

use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(CurrencyService::class)
            ->shouldReceive('supportedCodes')
            ->andReturn(['BRL', 'USD', 'GBP', 'JPY', 'CAD']);
    }

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

    public function test_register_currency_must_be_three_letters(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john3@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'BR',
            'currency' => 'BR1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);

        $this->assertDatabaseMissing('users', [
            'email' => 'john3@example.com',
        ]);
    }

    public function test_register_currency_must_be_supported(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john4@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'BR',
            'currency' => 'ZZZ',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);

        $this->assertDatabaseMissing('users', [
            'email' => 'john4@example.com',
        ]);
    }
}
