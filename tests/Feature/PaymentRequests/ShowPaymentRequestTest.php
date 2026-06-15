<?php

namespace Tests\Feature\PaymentRequests;

use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowPaymentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_payment_request_requires_authentication(): void
    {
        $paymentRequest = PaymentRequest::factory()->create();

        $response = $this->getJson("/api/payment-requests/{$paymentRequest->id}");

        $response->assertStatus(401);
    }

    public function test_employee_can_see_their_own_payment_request(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $token = $employee->createToken('auth_token')->accessToken;
        $paymentRequest = PaymentRequest::factory()->create(['user_id' => $employee->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/payment-requests/{$paymentRequest->id}");

        $response->assertOk()
            ->assertJsonPath('id', $paymentRequest->id)
            ->assertJsonPath('user_id', $employee->id)
            ->assertJsonPath('status', $paymentRequest->status->value);
    }

    public function test_employee_cannot_see_another_users_payment_request(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $otherEmployee = User::factory()->create(['role' => 'employee']);
        $token = $employee->createToken('auth_token')->accessToken;
        $paymentRequest = PaymentRequest::factory()->create(['user_id' => $otherEmployee->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/payment-requests/{$paymentRequest->id}");

        $response->assertStatus(403);
    }

    public function test_finance_can_see_any_payment_request(): void
    {
        $finance = User::factory()->finance()->create();
        $token = $finance->createToken('auth_token')->accessToken;
        $paymentRequest = PaymentRequest::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/payment-requests/{$paymentRequest->id}");

        $response->assertOk()
            ->assertJsonPath('id', $paymentRequest->id);
    }

    public function test_show_payment_request_returns_not_found_for_unknown_id(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $token = $employee->createToken('auth_token')->accessToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/payment-requests/999999');

        $response->assertStatus(404);
    }
}
