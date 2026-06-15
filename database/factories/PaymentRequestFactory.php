<?php

namespace Database\Factories;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentRequest>
 */
class PaymentRequestFactory extends Factory
{
    protected $model = PaymentRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount_local' => $this->faker->randomFloat(2, 100, 5000),
            'currency' => $this->faker->currencyCode(),
            'amount_eur' => $this->faker->randomFloat(2, 50, 2000),
            'eur_to_local_rate' => $this->faker->randomFloat(4, 0.5, 2.5),
            'rate_source' => 'external_api',
            'rate_fetched_at' => now()->subHour(),
            'status' => PaymentRequestStatus::PENDING->value,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'expired_at' => null,
        ];
    }
}
