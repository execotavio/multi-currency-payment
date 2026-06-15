<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\ExchangeRateProviderException;
use Illuminate\Http\Request;
use Tests\TestCase;

class ExchangeRateProviderExceptionTest extends TestCase
{
    public function test_it_renders_provider_failures_as_bad_gateway_json(): void
    {
        $exception = ExchangeRateProviderException::forProviderFailure('quota-reached');

        $response = $exception->render(Request::create('/api/payment-requests', 'POST'));
        $payload = $response->getData(true);

        $this->assertSame(502, $response->getStatusCode());
        $this->assertSame('Exchange rate provider failed: quota-reached', $payload['message']);
        $this->assertSame('exchangerate-api', $payload['provider']);
    }
}
