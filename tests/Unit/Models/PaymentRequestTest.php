<?php

namespace Tests\Unit\Models;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\PaymentRequestStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_casts_status_to_the_enum(): void
    {
        $request = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::APPROVED->value,
        ]);

        $this->assertInstanceOf(PaymentRequestStatus::class, $request->status);
        $this->assertSame(PaymentRequestStatus::APPROVED, $request->status);
        $this->assertSame('approved', $request->status->value);
    }

    public function test_it_exposes_state_helper_methods(): void
    {
        $pending = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::PENDING->value]);
        $approved = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::APPROVED->value]);
        $rejected = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::REJECTED->value]);
        $expired = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::EXPIRED->value]);

        $this->assertTrue($pending->isPending());
        $this->assertTrue($approved->isApproved());
        $this->assertTrue($rejected->isRejected());
        $this->assertTrue($expired->isExpired());
    }

    public function test_finance_user_can_approve_and_reject_pending_requests(): void
    {
        $service = app(PaymentRequestStateService::class);
        $reviewer = User::factory()->finance()->create();

        $approvedRequest = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::PENDING->value]);
        $service->approve($approvedRequest, $reviewer);

        $this->assertSame(PaymentRequestStatus::APPROVED, $approvedRequest->fresh()->status);
        $this->assertSame($reviewer->id, $approvedRequest->fresh()->reviewed_by);
        $this->assertNotNull($approvedRequest->fresh()->reviewed_at);

        $rejectedRequest = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::PENDING->value]);
        $service->reject($rejectedRequest, $reviewer);

        $this->assertSame(PaymentRequestStatus::REJECTED, $rejectedRequest->fresh()->status);
        $this->assertSame($reviewer->id, $rejectedRequest->fresh()->reviewed_by);
        $this->assertNotNull($rejectedRequest->fresh()->reviewed_at);
    }

    public function test_non_finance_user_cannot_approve_or_reject(): void
    {
        $service = app(PaymentRequestStateService::class);
        $reviewer = User::factory()->create(['role' => 'employee']);
        $request = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::PENDING->value]);

        $this->expectException(\DomainException::class);
        $service->approve($request, $reviewer);
    }

    public function test_only_pending_requests_can_be_approved_or_rejected(): void
    {
        $service = app(PaymentRequestStateService::class);
        $reviewer = User::factory()->finance()->create();
        $approvedRequest = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::APPROVED->value]);

        $this->expectException(\DomainException::class);
        $service->approve($approvedRequest, $reviewer);
    }

    public function test_expire_transitions_pending_requests_to_expired(): void
    {
        $service = app(PaymentRequestStateService::class);
        $request = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::PENDING->value]);

        $service->expire($request);

        $this->assertSame(PaymentRequestStatus::EXPIRED, $request->fresh()->status);
        $this->assertNotNull($request->fresh()->expired_at);
    }

    public function test_immutable_exchange_fields_cannot_be_changed_after_creation(): void
    {
        $service = app(PaymentRequestStateService::class);
        $request = PaymentRequest::factory()->create([
            'amount_eur' => 100.00,
            'eur_to_local_rate' => 1.20,
            'rate_source' => 'manual',
            'rate_fetched_at' => now()->subDay(),
        ]);

        $this->expectException(\DomainException::class);
        $service->assertImmutableFieldsUnchanged($request, ['amount_eur' => 999.00]);
    }
}
