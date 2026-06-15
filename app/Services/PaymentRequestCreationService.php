<?php

namespace App\Services;

use App\DTOs\RateDTO;
use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;

class PaymentRequestCreationService
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
    ) {
    }

    public function create(User $user, float|string $amountLocal, string $currency): PaymentRequest
    {
        $currency = strtoupper(trim($currency));
        $rate = $this->exchangeRateService->getEurTo($currency);

        return PaymentRequest::create([
            'user_id' => $user->id,
            'amount_local' => $this->formatDecimal($amountLocal),
            'currency' => $currency,
            'amount_eur' => $this->calculateAmountEur($amountLocal, $rate),
            'eur_to_local_rate' => $rate->rate,
            'rate_source' => $rate->source,
            'rate_fetched_at' => $rate->fetchedAt,
            'status' => PaymentRequestStatus::PENDING,
        ]);
    }

    private function calculateAmountEur(float|string $amountLocal, RateDTO $rate): string
    {
        return $this->formatDecimal((float) $amountLocal / $rate->rate);
    }

    private function formatDecimal(float|string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
