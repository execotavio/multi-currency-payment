<?php

namespace Tests\Unit\Services;

use App\Exceptions\ExchangeRateProviderException;
use App\Services\ExchangeRateService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.exchange_rate.base_url', 'https://v6.exchangerate-api.test/v6');
        config()->set('services.exchange_rate.api_key', 'test-key');
        config()->set('services.exchange_rate.timeout', 5);
        config()->set('services.exchange_rate.cache_ttl', 3600);
        config()->set('services.exchange_rate.cache_store', 'array');

        Cache::store('array')->flush();
    }

    public function test_fetches_rate_from_provider(): void
    {
        Carbon::setTestNow('2026-06-15 10:00:00');

        Http::fake([
            'https://v6.exchangerate-api.test/v6/test-key/pair/EUR/USD' => Http::response([
                'result' => 'success',
                'base_code' => 'EUR',
                'target_code' => 'USD',
                'conversion_rate' => 1.234567,
            ]),
        ]);

        $rate = app(ExchangeRateService::class)->getEurTo('USD');

        $this->assertGreaterThan(0, $rate->rate);
        $this->assertSame(1.234567, $rate->rate);
        $this->assertSame('exchangerate-api', $rate->source);
        $this->assertInstanceOf(Carbon::class, $rate->fetchedAt);
        $this->assertTrue($rate->fetchedAt->isSameSecond(now()));
        $this->assertFalse(Cache::store('array')->has('exchange_rate:eur_to:USD'));

        Http::assertSentCount(1);
    }

    public function test_each_call_fetches_a_fresh_rate_from_provider(): void
    {
        Http::fakeSequence()
            ->push([
                'result' => 'success',
                'conversion_rate' => 1.1,
            ])
            ->push([
                'result' => 'success',
                'conversion_rate' => 1.2,
            ]);

        $service = app(ExchangeRateService::class);

        $firstRate = $service->getEurTo('USD');
        $secondRate = $service->getEurTo('USD');

        $this->assertSame(1.1, $firstRate->rate);
        $this->assertSame(1.2, $secondRate->rate);
        $this->assertFalse(Cache::store('array')->has('exchange_rate:eur_to:USD'));
        Http::assertSentCount(2);
    }

    public function test_eur_to_eur_returns_one_without_calling_provider(): void
    {
        Carbon::setTestNow('2026-06-15 10:00:00');
        Http::fake();

        $rate = app(ExchangeRateService::class)->getEurTo(' eur ');

        $this->assertSame(1.0, $rate->rate);
        $this->assertSame('exchangerate-api', $rate->source);
        $this->assertTrue($rate->fetchedAt->isSameSecond(now()));
        $this->assertFalse(Cache::store('array')->has('exchange_rate:eur_to:EUR'));

        Http::assertNothingSent();
    }

    public function test_connection_failures_throw_provider_exception(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('timed out');
        });

        $this->expectException(ExchangeRateProviderException::class);
        $this->expectExceptionMessage('timed out');

        app(ExchangeRateService::class)->getEurTo('USD');
    }

    public function test_http_errors_throw_provider_exception(): void
    {
        Http::fake([
            'https://v6.exchangerate-api.test/v6/test-key/pair/EUR/USD' => Http::response([], 500),
        ]);

        $this->expectException(ExchangeRateProviderException::class);
        $this->expectExceptionMessage('HTTP 500');

        app(ExchangeRateService::class)->getEurTo('USD');
    }

    public function test_provider_error_response_throws_provider_exception(): void
    {
        Http::fake([
            'https://v6.exchangerate-api.test/v6/test-key/pair/EUR/USD' => Http::response([
                'result' => 'error',
                'error-type' => 'quota-reached',
            ]),
        ]);

        $this->expectException(ExchangeRateProviderException::class);
        $this->expectExceptionMessage('quota-reached');

        app(ExchangeRateService::class)->getEurTo('USD');
    }

    #[DataProvider('invalidRatePayloads')]
    public function test_missing_or_non_positive_conversion_rate_throws_provider_exception(array $payload): void
    {
        Http::fake([
            'https://v6.exchangerate-api.test/v6/test-key/pair/EUR/USD' => Http::response($payload),
        ]);

        $this->expectException(ExchangeRateProviderException::class);
        $this->expectExceptionMessage('invalid conversion rate');

        app(ExchangeRateService::class)->getEurTo('USD');
    }

    public function test_currency_is_normalized_before_fetching(): void
    {
        Http::fake([
            'https://v6.exchangerate-api.test/v6/test-key/pair/EUR/USD' => Http::response([
                'result' => 'success',
                'conversion_rate' => 1.2,
            ]),
        ]);

        app(ExchangeRateService::class)->getEurTo(' usd ');

        $this->assertFalse(Cache::store('array')->has('exchange_rate:eur_to:USD'));
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://v6.exchangerate-api.test/v6/test-key/pair/EUR/USD';
        });
    }

    public function test_invalid_currency_throws_invalid_argument_exception(): void
    {
        Http::fake();

        $this->expectException(InvalidArgumentException::class);

        app(ExchangeRateService::class)->getEurTo('US1');
    }

    public static function invalidRatePayloads(): array
    {
        return [
            'missing conversion rate' => [
                ['result' => 'success'],
            ],
            'zero conversion rate' => [
                ['result' => 'success', 'conversion_rate' => 0],
            ],
            'negative conversion rate' => [
                ['result' => 'success', 'conversion_rate' => -1],
            ],
        ];
    }
}
