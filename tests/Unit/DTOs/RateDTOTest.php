<?php

namespace Tests\Unit\DTOs;

use App\DTOs\RateDTO;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RateDTOTest extends TestCase
{
    public function test_it_preserves_rate_source_and_fetched_at(): void
    {
        $fetchedAt = Carbon::parse('2026-06-15 10:00:00');

        $dto = new RateDTO(
            rate: 6.123456,
            source: 'exchangerate-api',
            fetchedAt: $fetchedAt,
        );

        $this->assertSame(6.123456, $dto->rate);
        $this->assertSame('exchangerate-api', $dto->source);
        $this->assertSame($fetchedAt, $dto->fetchedAt);
    }
}
