<?php

namespace App\Exceptions;

use RuntimeException;

class ExchangeRateProviderException extends RuntimeException
{
    public static function forProviderFailure(string $reason): self
    {
        return new self(sprintf('Exchange rate provider failed: %s', $reason));
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'provider' => 'exchangerate-api',
        ], 502);
    }
}
