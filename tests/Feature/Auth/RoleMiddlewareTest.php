<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_route_returns_401_without_token(): void
    {
        $response = $this->getJson('/api/auth/finance-only');

        $response->assertStatus(401);
    }

    public function test_finance_route_returns_403_for_employee_user(): void
    {
        $user = User::factory()->create([
            'role' => 'employee',
        ]);

        $token = $user->createToken('auth_token')->accessToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/finance-only');

        $response->assertStatus(403);
    }

    public function test_finance_route_allows_finance_user(): void
    {
        $user = User::factory()->finance()->create();
        $token = $user->createToken('auth_token')->accessToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/finance-only');

        $response->assertOk();
    }
}
