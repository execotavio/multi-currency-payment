<?php

namespace Tests\Feature\PaymentRequests;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RejectPaymentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_can_reject_pending_payment_request(): void
    {
        $finance = User::factory()->finance()->create();
        $token = $finance->createToken('auth_token')->accessToken;
        $paymentRequest = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::PENDING->value,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/payment-requests/{$paymentRequest->id}/reject");

        $response->assertOk()
            ->assertJsonPath('status', 'rejected')
            ->assertJsonPath('reviewed_by', $finance->id)
            ->assertJsonStructure(['reviewed_at']);

        $paymentRequest->refresh();

        $this->assertSame(PaymentRequestStatus::REJECTED, $paymentRequest->status);
        $this->assertSame($finance->id, $paymentRequest->reviewed_by);
        $this->assertNotNull($paymentRequest->reviewed_at);
    }

    public function test_employee_cannot_reject_payment_request(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $token = $employee->createToken('auth_token')->accessToken;
        $paymentRequest = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::PENDING->value,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/payment-requests/{$paymentRequest->id}/reject");

        $response->assertStatus(403);
    }

    #[DataProvider('nonPendingStatuses')]
    public function test_finance_cannot_reject_non_pending_payment_request(PaymentRequestStatus $status): void
    {
        $finance = User::factory()->finance()->create();
        $token = $finance->createToken('auth_token')->accessToken;
        $paymentRequest = PaymentRequest::factory()->create([
            'status' => $status->value,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/payment-requests/{$paymentRequest->id}/reject");

        $response->assertStatus(409)
            ->assertJsonPath('message', 'Only pending payment requests can be reject');
    }

    public function test_reject_requires_authentication(): void
    {
        $paymentRequest = PaymentRequest::factory()->create();

        $response = $this->postJson("/api/payment-requests/{$paymentRequest->id}/reject");

        $response->assertStatus(401);
    }

    public function test_reject_returns_not_found_for_unknown_id(): void
    {
        $finance = User::factory()->finance()->create();
        $token = $finance->createToken('auth_token')->accessToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/payment-requests/999999/reject');

        $response->assertStatus(404);
    }

    public static function nonPendingStatuses(): array
    {
        return [
            'approved' => [PaymentRequestStatus::APPROVED],
            'rejected' => [PaymentRequestStatus::REJECTED],
            'expired' => [PaymentRequestStatus::EXPIRED],
        ];
    }
}
