<?php

namespace Tests\Feature\PaymentRequests;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExpirePendingPaymentRequestsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_expires_pending_payment_requests_older_than_48_hours(): void
    {
        $now = Carbon::parse('2026-06-15 12:00:00');
        $oldTimestamp = $now->copy()->subHours(49);
        $this->travelTo($now);

        $paymentRequest = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::PENDING->value,
            'created_at' => $oldTimestamp,
            'updated_at' => $oldTimestamp,
            'expired_at' => null,
        ]);

        $this->artisan('payments:expire-pending')
            ->expectsOutput('Expired 1 pending payment requests.')
            ->assertExitCode(0);

        $paymentRequest->refresh();

        $this->assertSame(PaymentRequestStatus::EXPIRED, $paymentRequest->status);
        $this->assertTrue($paymentRequest->expired_at->isSameSecond($now));
        $this->assertTrue($paymentRequest->updated_at->isSameSecond($now));
    }

    public function test_it_keeps_pending_payment_requests_that_are_not_older_than_48_hours(): void
    {
        $now = Carbon::parse('2026-06-15 12:00:00');
        $this->travelTo($now);

        $exactlyAtCutoff = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::PENDING->value,
            'created_at' => $now->copy()->subHours(48),
            'updated_at' => $now->copy()->subHours(48),
            'expired_at' => null,
        ]);
        $recent = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::PENDING->value,
            'created_at' => $now->copy()->subHours(47),
            'updated_at' => $now->copy()->subHours(47),
            'expired_at' => null,
        ]);

        $this->artisan('payments:expire-pending')
            ->expectsOutput('Expired 0 pending payment requests.')
            ->assertExitCode(0);

        $this->assertSame(PaymentRequestStatus::PENDING, $exactlyAtCutoff->fresh()->status);
        $this->assertNull($exactlyAtCutoff->fresh()->expired_at);
        $this->assertSame(PaymentRequestStatus::PENDING, $recent->fresh()->status);
        $this->assertNull($recent->fresh()->expired_at);
    }

    public function test_it_does_not_change_old_non_pending_payment_requests(): void
    {
        $now = Carbon::parse('2026-06-15 12:00:00');
        $oldTimestamp = $now->copy()->subHours(49);
        $this->travelTo($now);

        $approved = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::APPROVED->value,
            'created_at' => $oldTimestamp,
            'updated_at' => $oldTimestamp,
            'expired_at' => null,
        ]);
        $rejected = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::REJECTED->value,
            'created_at' => $oldTimestamp,
            'updated_at' => $oldTimestamp,
            'expired_at' => null,
        ]);
        $expired = PaymentRequest::factory()->create([
            'status' => PaymentRequestStatus::EXPIRED->value,
            'created_at' => $oldTimestamp,
            'updated_at' => $oldTimestamp,
            'expired_at' => $oldTimestamp,
        ]);

        $this->artisan('payments:expire-pending')
            ->expectsOutput('Expired 0 pending payment requests.')
            ->assertExitCode(0);

        $this->assertSame(PaymentRequestStatus::APPROVED, $approved->fresh()->status);
        $this->assertNull($approved->fresh()->expired_at);
        $this->assertTrue($approved->fresh()->updated_at->isSameSecond($oldTimestamp));
        $this->assertSame(PaymentRequestStatus::REJECTED, $rejected->fresh()->status);
        $this->assertNull($rejected->fresh()->expired_at);
        $this->assertTrue($rejected->fresh()->updated_at->isSameSecond($oldTimestamp));
        $this->assertSame(PaymentRequestStatus::EXPIRED, $expired->fresh()->status);
        $this->assertTrue($expired->fresh()->expired_at->isSameSecond($oldTimestamp));
        $this->assertTrue($expired->fresh()->updated_at->isSameSecond($oldTimestamp));
    }

    public function test_it_processes_more_than_one_chunk(): void
    {
        $now = Carbon::parse('2026-06-15 12:00:00');
        $oldTimestamp = $now->copy()->subHours(49);
        $this->travelTo($now);

        $user = User::factory()->create();
        PaymentRequest::factory()
            ->count(501)
            ->create([
                'user_id' => $user->id,
                'status' => PaymentRequestStatus::PENDING->value,
                'created_at' => $oldTimestamp,
                'updated_at' => $oldTimestamp,
                'expired_at' => null,
            ]);

        $this->artisan('payments:expire-pending')
            ->expectsOutput('Expired 501 pending payment requests.')
            ->assertExitCode(0);

        $this->assertSame(501, PaymentRequest::query()
            ->where('status', PaymentRequestStatus::EXPIRED->value)
            ->where('expired_at', $now)
            ->where('updated_at', $now)
            ->count());
    }
}
