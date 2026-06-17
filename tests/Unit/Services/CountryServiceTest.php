<?php

namespace Tests\Unit\Services;

use App\Exceptions\CountryProviderException;
use App\Services\CountryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CountryServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.rest_countries.base_url', 'https://api.restcountries.test/countries/v5');
        config()->set('services.rest_countries.api_key', 'test-key');
        config()->set('services.rest_countries.timeout', 5);
        config()->set('services.rest_countries.cache_ttl', 3600);
        config()->set('services.rest_countries.cache_store', 'array');

        Cache::store('array')->flush();
    }

    public function test_it_fetches_supported_countries_from_provider_and_caches_them(): void
    {
        Http::fake([
            'https://api.restcountries.test/countries/v5*' => Http::response([
                'data' => [
                    'objects' => [
                        ['names.common' => 'Brazil', 'codes.alpha_2' => 'BR'],
                        ['names.common' => 'Canada', 'codes.alpha_2' => 'CA'],
                        ['names.common' => 'United States', 'codes.alpha_2' => 'US'],
                    ],
                    'meta' => ['more' => false],
                ],
            ]),
        ]);

        $countries = app(CountryService::class)->supportedCountries();

        $this->assertSame([
            ['code' => 'BR', 'name' => 'Brazil'],
            ['code' => 'CA', 'name' => 'Canada'],
            ['code' => 'US', 'name' => 'United States'],
        ], $countries);
        $this->assertTrue(Cache::store('array')->has('rest_countries:supported_countries'));
        Http::assertSentCount(1);
    }

    public function test_it_fetches_all_pages_from_provider(): void
    {
        Http::fakeSequence()
            ->push([
                'data' => [
                    'objects' => [
                        ['names.common' => 'Brazil', 'codes.alpha_2' => 'BR'],
                    ],
                    'meta' => ['more' => true],
                ],
            ])
            ->push([
                'data' => [
                    'objects' => [
                        ['names.common' => 'United States', 'codes.alpha_2' => 'US'],
                    ],
                    'meta' => ['more' => false],
                ],
            ]);

        $this->assertSame(['BR', 'US'], app(CountryService::class)->supportedCodes());
        Http::assertSentCount(2);
    }

    public function test_cache_hit_returns_supported_countries_without_calling_provider(): void
    {
        Cache::store('array')->put('rest_countries:supported_countries', [
            ['code' => 'BR', 'name' => 'Brazil'],
        ], 3600);
        Http::fake();

        $this->assertSame(['BR'], app(CountryService::class)->supportedCodes());
        Http::assertNothingSent();
    }

    public function test_missing_api_key_throws_provider_exception(): void
    {
        config()->set('services.rest_countries.api_key', null);

        $this->expectException(CountryProviderException::class);
        $this->expectExceptionMessage('missing API key');

        app(CountryService::class)->supportedCountries();
    }

    public function test_invalid_payload_throws_provider_exception(): void
    {
        Http::fake([
            'https://api.restcountries.test/countries/v5*' => Http::response([
                'data' => ['objects' => []],
            ]),
        ]);

        $this->expectException(CountryProviderException::class);
        $this->expectExceptionMessage('invalid country payload');

        app(CountryService::class)->supportedCountries();
    }
}
