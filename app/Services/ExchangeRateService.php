<?php

namespace App\Services;

use App\DTOs\RateDTO;
use App\Exceptions\ExchangeRateProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class ExchangeRateService
{
    private const SOURCE = 'exchangerate-api';

    public function getEurTo(string $currency): RateDTO
    {
        $currency = $this->normalizeCurrency($currency);

        if ($currency === 'EUR') {
            return new RateDTO(
                rate: 1.0,
                source: self::SOURCE,
                fetchedAt: now(),
            );
        }

        $cacheKey = sprintf('exchange_rate:eur_to:%s', $currency);

        return Cache::store((string) config('services.exchange_rate.cache_store'))
            ->remember(
                $cacheKey,
                (int) config('services.exchange_rate.cache_ttl'),
                fn (): RateDTO => $this->fetchEurTo($currency),
            );
    }

    private function normalizeCurrency(string $currency): string
    {
        $currency = strtoupper(trim($currency));

        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('Currency must be a valid ISO 4217 three-letter code.');
        }

        return $currency;
    }

    private function fetchEurTo(string $currency): RateDTO
    {
        $apiKey = config('services.exchange_rate.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw ExchangeRateProviderException::forProviderFailure('missing API key');
        }

        try {
            $response = Http::timeout((int) config('services.exchange_rate.timeout'))
                ->acceptJson()
                ->get($this->pairEndpoint($apiKey, $currency));
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

        $rate = $payload['conversion_rate'] ?? null;

        if (! is_numeric($rate) || (float) $rate <= 0) {
            throw ExchangeRateProviderException::forProviderFailure('invalid conversion rate');
        }

        return new RateDTO(
            rate: (float) $rate,
            source: self::SOURCE,
            fetchedAt: now(),
        );
    }

    private function pairEndpoint(string $apiKey, string $currency): string
    {
        return sprintf(
            '%s/%s/pair/EUR/%s',
            rtrim((string) config('services.exchange_rate.base_url'), '/'),
            $apiKey,
            $currency,
        );
    }
}
