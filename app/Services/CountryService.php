<?php

namespace App\Services;

use App\Exceptions\CountryProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CountryService
{
    /**
     * @return array<int, array{code: string, name: string}>
     */
    public function supportedCountries(): array
    {
        return Cache::store((string) config('services.rest_countries.cache_store'))
            ->remember(
                'rest_countries:supported_countries',
                (int) config('services.rest_countries.cache_ttl'),
                fn (): array => $this->fetchSupportedCountries(),
            );
    }

    /**
     * @return array<int, string>
     */
    public function supportedCodes(): array
    {
        return array_column($this->supportedCountries(), 'code');
    }

    /**
     * @return array<int, array{code: string, name: string}>
     */
    private function fetchSupportedCountries(): array
    {
        $apiKey = config('services.rest_countries.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw CountryProviderException::forProviderFailure('missing API key');
        }

        $countries = [];
        $offset = 0;
        $limit = 100;

        do {
            $payload = $this->fetchPage($apiKey, $limit, $offset);
            $objects = $payload['data']['objects'] ?? null;

            if (! is_array($objects)) {
                throw CountryProviderException::forProviderFailure('invalid country payload');
            }

            foreach ($objects as $country) {
                if (! is_array($country)) {
                    continue;
                }

                $code = $country['codes.alpha_2'] ?? $country['codes']['alpha_2'] ?? null;
                $name = $country['names.common'] ?? $country['names']['common'] ?? null;

                if (
                    is_string($code)
                    && is_string($name)
                    && preg_match('/^[A-Z]{2}$/', $code) === 1
                    && trim($name) !== ''
                ) {
                    $countries[] = [
                        'code' => $code,
                        'name' => $name,
                    ];
                }
            }

            $meta = $payload['data']['meta'] ?? [];
            $hasMore = is_array($meta) && ($meta['more'] ?? false) === true;
            $offset += $limit;
        } while ($hasMore);

        $countries = collect($countries)
            ->unique('code')
            ->sortBy('name')
            ->values()
            ->all();

        if ($countries === []) {
            throw CountryProviderException::forProviderFailure('invalid country payload');
        }

        return $countries;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPage(string $apiKey, int $limit, int $offset): array
    {
        try {
            $response = Http::timeout((int) config('services.rest_countries.timeout'))
                ->acceptJson()
                ->withToken($apiKey)
                ->get($this->countriesEndpoint(), [
                    'response_fields' => 'names.common,codes.alpha_2',
                    'limit' => $limit,
                    'offset' => $offset,
                ]);
        } catch (ConnectionException $exception) {
            throw CountryProviderException::forProviderFailure($exception->getMessage());
        }

        if (! $response->successful()) {
            throw CountryProviderException::forProviderFailure(sprintf('HTTP %s', $response->status()));
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw CountryProviderException::forProviderFailure('invalid country payload');
        }

        return $payload;
    }

    private function countriesEndpoint(): string
    {
        return rtrim((string) config('services.rest_countries.base_url'), '/');
    }
}
