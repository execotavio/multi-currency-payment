<?php

namespace App\DTOs;

use Illuminate\Support\Carbon;

readonly class RateDTO
{
    public function __construct(
        public float $rate,
        public string $source,
        public Carbon $fetchedAt,
    ) {
    }
}
