<?php

namespace App\Services;

use App\Exceptions\ExchangeRateProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    /**
     * @return array<int, array{code: string, name: string}>
     */
    public function supportedCurrencies(): array
    {
        return Cache::store((string) config('services.exchange_rate.cache_store'))
            ->remember(
                'exchange_rate:supported_codes',
                (int) config('services.exchange_rate.cache_ttl'),
                fn (): array => $this->fetchSupportedCurrencies(),
            );
    }

    /**
     * @return array<int, string>
     */
    public function supportedCodes(): array
    {
        return array_column($this->supportedCurrencies(), 'code');
    }

    /**
     * @return array<int, array{code: string, name: string}>
     */
    private function fetchSupportedCurrencies(): array
    {
        $apiKey = config('services.exchange_rate.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw ExchangeRateProviderException::forProviderFailure('missing API key');
        }

        try {
            $response = Http::timeout((int) config('services.exchange_rate.timeout'))
                ->acceptJson()
                ->get($this->codesEndpoint($apiKey));
        } catch (ConnectionException $exception) {
            throw ExchangeRateProviderException::forProviderFailure($exception->getMessage());
        }

        if (! $response->successful()) {
            throw ExchangeRateProviderException::forProviderFailure(sprintf('HTTP %s', $response->status()));
        }

        $payload = $response->json();

        if (($payload['result'] ?? null) !== 'success') {
            throw ExchangeRateProviderException::forProviderFailure((string) ($payload['error-type'] ?? 'unexpected response'));
        }

        $currencies = collect($payload['supported_codes'] ?? [])
            ->filter(fn (mixed $currency): bool => is_array($currency)
                && isset($currency[0], $currency[1])
                && is_string($currency[0])
                && is_string($currency[1])
                && preg_match('/^[A-Z]{3}$/', $currency[0]) === 1
            )
            ->map(fn (array $currency): array => [
                'code' => $currency[0],
                'name' => $currency[1],
            ])
            ->unique('code')
            ->sortBy('code')
            ->values()
            ->all();

        if ($currencies === []) {
            throw ExchangeRateProviderException::forProviderFailure('invalid supported codes');
        }

        return $currencies;
    }

    private function codesEndpoint(string $apiKey): string
    {
        return sprintf(
            '%s/%s/codes',
            rtrim((string) config('services.exchange_rate.base_url'), '/'),
            $apiKey,
        );
    }
}
