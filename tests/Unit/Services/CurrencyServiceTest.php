<?php

namespace Tests\Unit\Services;

use App\Exceptions\ExchangeRateProviderException;
use App\Services\CurrencyService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
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

    public function test_it_fetches_supported_currencies_from_provider_and_caches_them(): void
    {
        Http::fake([
            'https://v6.exchangerate-api.test/v6/test-key/codes' => Http::response([
                'result' => 'success',
                'supported_codes' => [
                    ['USD', 'US Dollar'],
                    ['BRL', 'Brazilian Real'],
                    ['JPY', 'Japanese Yen'],
                ],
            ]),
        ]);

        $currencies = app(CurrencyService::class)->supportedCurrencies();

        $this->assertSame([
            ['code' => 'BRL', 'name' => 'Brazilian Real'],
            ['code' => 'JPY', 'name' => 'Japanese Yen'],
            ['code' => 'USD', 'name' => 'US Dollar'],
        ], $currencies);
        $this->assertTrue(Cache::store('array')->has('exchange_rate:supported_codes'));

        Http::assertSentCount(1);
    }

    public function test_cache_hit_returns_supported_currencies_without_calling_provider(): void
    {
        Cache::store('array')->put('exchange_rate:supported_codes', [
            ['code' => 'USD', 'name' => 'US Dollar'],
        ], 3600);
        Http::fake();

        $this->assertSame(['USD'], app(CurrencyService::class)->supportedCodes());
        Http::assertNothingSent();
    }

    public function test_provider_failures_throw_provider_exception(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('timed out');
        });

        $this->expectException(ExchangeRateProviderException::class);
        $this->expectExceptionMessage('timed out');

        app(CurrencyService::class)->supportedCurrencies();
    }

    public function test_provider_error_response_throws_provider_exception(): void
    {
        Http::fake([
            'https://v6.exchangerate-api.test/v6/test-key/codes' => Http::response([
                'result' => 'error',
                'error-type' => 'quota-reached',
            ]),
        ]);

        $this->expectException(ExchangeRateProviderException::class);
        $this->expectExceptionMessage('quota-reached');

        app(CurrencyService::class)->supportedCurrencies();
    }

    public function test_invalid_supported_codes_payload_throws_provider_exception(): void
    {
        Http::fake([
            'https://v6.exchangerate-api.test/v6/test-key/codes' => Http::response([
                'result' => 'success',
                'supported_codes' => [],
            ]),
        ]);

        $this->expectException(ExchangeRateProviderException::class);
        $this->expectExceptionMessage('invalid supported codes');

        app(CurrencyService::class)->supportedCurrencies();
    }
}
