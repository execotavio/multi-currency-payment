<?php

namespace Tests\Feature\Currencies;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListCurrenciesTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_currencies_requires_authentication(): void
    {
        $response = $this->getJson('/api/currencies');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_supported_currencies(): void
    {
        config()->set('services.exchange_rate.base_url', 'https://v6.exchangerate-api.test/v6');
        config()->set('services.exchange_rate.api_key', 'test-key');
        config()->set('services.exchange_rate.cache_store', 'array');

        Http::fake([
            'https://v6.exchangerate-api.test/v6/test-key/codes' => Http::response([
                'result' => 'success',
                'supported_codes' => [
                    ['BRL', 'Brazilian Real'],
                    ['USD', 'US Dollar'],
                    ['GBP', 'British Pound'],
                    ['JPY', 'Japanese Yen'],
                    ['CAD', 'Canadian Dollar'],
                    ['AED', 'UAE Dirham'],
                ],
            ]),
        ]);

        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->accessToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/currencies');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['code', 'name'],
                ],
            ]);

        $codes = collect($response->json('data'))->pluck('code');

        foreach (['BRL', 'USD', 'GBP', 'JPY', 'CAD'] as $code) {
            $this->assertTrue($codes->contains($code), "{$code} is missing from supported currencies.");
        }
        $this->assertGreaterThan(5, $codes->count());
    }
}
