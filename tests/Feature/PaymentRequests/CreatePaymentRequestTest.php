<?php

namespace Tests\Feature\PaymentRequests;

use App\DTOs\RateDTO;
use App\Exceptions\ExchangeRateProviderException;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\CurrencyService;
use App\Services\ExchangeRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CreatePaymentRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(CurrencyService::class)
            ->shouldReceive('supportedCodes')
            ->andReturn(['BRL', 'USD', 'GBP', 'JPY', 'CAD']);
    }

    public function test_employee_can_create_payment_request_with_exchange_rate_fields(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $token = $user->createToken('auth_token')->accessToken;
        $fetchedAt = Carbon::parse('2026-06-15 10:00:00');

        $this->mock(ExchangeRateService::class)
            ->shouldReceive('getEurTo')
            ->once()
            ->with('BRL')
            ->andReturn(new RateDTO(
                rate: 5.5,
                source: 'exchangerate-api',
                fetchedAt: $fetchedAt,
            ));

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/payment-requests', [
                'amount_local' => 110,
                'currency' => 'BRL',
            ]);

        $response->assertCreated()
            ->assertJsonPath('amount_local', '110.00')
            ->assertJsonPath('currency', 'BRL')
            ->assertJsonPath('amount_eur', '20.00')
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('eur_to_local_rate', '5.500000')
            ->assertJsonPath('rate_source', 'exchangerate-api')
            ->assertJsonStructure([
                'id',
                'amount_local',
                'currency',
                'amount_eur',
                'status',
                'eur_to_local_rate',
                'rate_source',
                'rate_fetched_at',
            ]);

        $this->assertDatabaseHas('payment_requests', [
            'user_id' => $user->id,
            'amount_local' => 110,
            'currency' => 'BRL',
            'amount_eur' => 20,
            'eur_to_local_rate' => 5.5,
            'rate_source' => 'exchangerate-api',
            'status' => 'pending',
        ]);
    }

    public function test_payment_request_creation_requires_authentication(): void
    {
        $response = $this->postJson('/api/payment-requests', [
            'amount_local' => 110,
            'currency' => 'BRL',
        ]);

        $response->assertStatus(401);
    }

    public function test_finance_user_cannot_create_payment_request(): void
    {
        $user = User::factory()->finance()->create();
        $token = $user->createToken('auth_token')->accessToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/payment-requests', [
                'amount_local' => 110,
                'currency' => 'BRL',
            ]);

        $response->assertStatus(403);
    }

    public function test_amount_local_must_be_positive(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $token = $user->createToken('auth_token')->accessToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/payment-requests', [
                'amount_local' => 0,
                'currency' => 'BRL',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount_local']);
    }

    public function test_currency_must_be_three_letters(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $token = $user->createToken('auth_token')->accessToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/payment-requests', [
                'amount_local' => 110,
                'currency' => 'BR1',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    }

    public function test_currency_must_be_supported(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $token = $user->createToken('auth_token')->accessToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/payment-requests', [
                'amount_local' => 110,
                'currency' => 'ZZZ',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    }

    public function test_provider_failure_returns_bad_gateway_and_does_not_persist_payment_request(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $token = $user->createToken('auth_token')->accessToken;

        $this->mock(ExchangeRateService::class)
            ->shouldReceive('getEurTo')
            ->once()
            ->with('BRL')
            ->andThrow(ExchangeRateProviderException::forProviderFailure('quota-reached'));

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/payment-requests', [
                'amount_local' => 110,
                'currency' => 'BRL',
            ]);

        $response->assertStatus(502)
            ->assertJsonPath('provider', 'exchangerate-api');

        $this->assertSame(0, PaymentRequest::count());
    }
}
