<?php

namespace Tests\Unit\Services;

use App\DTOs\RateDTO;
use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\ExchangeRateService;
use App\Services\PaymentRequestCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PaymentRequestCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_payment_request_with_exchange_rate_fields_and_pending_status(): void
    {
        $fetchedAt = Carbon::parse('2026-06-15 10:00:00');
        $user = User::factory()->create();

        $this->mock(ExchangeRateService::class)
            ->shouldReceive('getEurTo')
            ->once()
            ->with('BRL')
            ->andReturn(new RateDTO(
                rate: 5.5,
                source: 'exchangerate-api',
                fetchedAt: $fetchedAt,
            ));

        $paymentRequest = app(PaymentRequestCreationService::class)
            ->create($user, '110.00', ' brl ');

        $this->assertSame($user->id, $paymentRequest->user_id);
        $this->assertSame('110.00', $paymentRequest->amount_local);
        $this->assertSame('BRL', $paymentRequest->currency);
        $this->assertSame('20.00', $paymentRequest->amount_eur);
        $this->assertSame('5.500000', $paymentRequest->eur_to_local_rate);
        $this->assertSame('exchangerate-api', $paymentRequest->rate_source);
        $this->assertTrue($paymentRequest->rate_fetched_at->isSameSecond($fetchedAt));
        $this->assertSame(PaymentRequestStatus::PENDING, $paymentRequest->status);
    }

    public function test_it_rounds_amount_eur_to_two_decimal_places(): void
    {
        $user = User::factory()->create();

        $this->mock(ExchangeRateService::class)
            ->shouldReceive('getEurTo')
            ->once()
            ->with('USD')
            ->andReturn(new RateDTO(
                rate: 3,
                source: 'exchangerate-api',
                fetchedAt: now(),
            ));

        $paymentRequest = app(PaymentRequestCreationService::class)
            ->create($user, '10.00', 'USD');

        $this->assertSame('3.33', $paymentRequest->amount_eur);
    }

    public function test_persisted_rate_does_not_change_when_provider_returns_a_different_rate_later(): void
    {
        $user = User::factory()->create();

        $this->mock(ExchangeRateService::class)
            ->shouldReceive('getEurTo')
            ->twice()
            ->with('USD')
            ->andReturn(
                new RateDTO(rate: 2, source: 'exchangerate-api', fetchedAt: Carbon::parse('2026-06-15 10:00:00')),
                new RateDTO(rate: 4, source: 'exchangerate-api', fetchedAt: Carbon::parse('2026-06-15 11:00:00')),
            );

        $service = app(PaymentRequestCreationService::class);

        $firstPaymentRequest = $service->create($user, '100.00', 'USD');
        $service->create($user, '100.00', 'USD');

        $this->assertSame('2.000000', $firstPaymentRequest->fresh()->eur_to_local_rate);
        $this->assertSame('50.00', $firstPaymentRequest->fresh()->amount_eur);
        $this->assertSame(2, PaymentRequest::count());
    }
}
