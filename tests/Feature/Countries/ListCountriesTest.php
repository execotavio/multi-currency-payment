<?php

namespace Tests\Feature\Countries;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ListCountriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_supported_countries(): void
    {
        config()->set('services.rest_countries.base_url', 'https://api.restcountries.test/countries/v5');
        config()->set('services.rest_countries.api_key', 'test-key');
        config()->set('services.rest_countries.cache_store', 'array');

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

        $response = $this->getJson('/api/countries');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['code', 'name'],
                ],
            ])
            ->assertJsonPath('data.0.code', 'BR')
            ->assertJsonPath('data.0.name', 'Brazil');
    }
}
