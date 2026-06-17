<?php

namespace App\Exceptions;

use RuntimeException;

class CountryProviderException extends RuntimeException
{
    public static function forProviderFailure(string $reason): self
    {
        return new self(sprintf('Country provider failed: %s', $reason));
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
            'provider' => 'restcountries',
        ], 502);
    }
}
