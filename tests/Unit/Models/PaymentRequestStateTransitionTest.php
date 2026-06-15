<?php

namespace Tests\Unit\Models;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\PaymentRequestStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentRequestStateTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_non_pending_transitions(): void
    {
        $service = app(PaymentRequestStateService::class);
        $reviewer = User::factory()->finance()->create();
        $request = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::APPROVED->value]);

        $this->expectException(\DomainException::class);
        $service->approve($request, $reviewer);
    }

    public function test_it_guard_immutable_fields_when_updating_the_model(): void
    {
        $request = PaymentRequest::factory()->create([
            'amount_eur' => 100.00,
            'eur_to_local_rate' => 1.20,
            'rate_source' => 'manual',
            'rate_fetched_at' => now()->subDay(),
        ]);

        $this->expectException(\DomainException::class);
        $request->fill(['amount_eur' => 150.00]);
        $request->save();
    }
}
