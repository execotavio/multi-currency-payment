<?php

namespace Tests\Feature\PaymentRequests;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPaymentRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_payment_requests_requires_authentication(): void
    {
        $response = $this->getJson('/api/payment-requests');

        $response->assertStatus(401);
    }

    public function test_employee_only_sees_their_own_payment_requests(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $otherEmployee = User::factory()->create(['role' => 'employee']);
        $token = $employee->createToken('auth_token')->accessToken;

        $ownRequest = PaymentRequest::factory()->create(['user_id' => $employee->id]);
        PaymentRequest::factory()->create(['user_id' => $otherEmployee->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/payment-requests');

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertSame([$ownRequest->id], $ids->all());
    }

    public function test_finance_sees_all_payment_requests(): void
    {
        $finance = User::factory()->finance()->create();
        $token = $finance->createToken('auth_token')->accessToken;

        $firstRequest = PaymentRequest::factory()->create();
        $secondRequest = PaymentRequest::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/payment-requests');

        $response->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertCount(2, $ids);
        $this->assertTrue($ids->contains($firstRequest->id));
        $this->assertTrue($ids->contains($secondRequest->id));
    }

    public function test_status_filter_returns_only_matching_payment_requests(): void
    {
        $finance = User::factory()->finance()->create();
        $token = $finance->createToken('auth_token')->accessToken;

        $pendingRequest = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::PENDING->value,
        ]);
        PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::APPROVED->value,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/payment-requests?status=pending');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $pendingRequest->id)
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_invalid_status_filter_returns_validation_error(): void
    {
        $finance = User::factory()->finance()->create();
        $token = $finance->createToken('auth_token')->accessToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/payment-requests?status=paid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
