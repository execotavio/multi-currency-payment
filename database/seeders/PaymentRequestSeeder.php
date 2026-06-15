<?php

namespace Database\Seeders;

use App\DTOs\RateDTO;
use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\ExchangeRateService;
use App\Services\PaymentRequestCreationService;
use App\Services\PaymentRequestStateService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class PaymentRequestSeeder extends Seeder
{
    /**
     * @var array<string, float>
     */
    private const SEEDED_RATES = [
        'BRL' => 5.50,
        'USD' => 1.10,
        'GBP' => 0.85,
        'JPY' => 170.00,
        'CAD' => 1.48,
    ];

    /**
     * @var array<int, string>
     */
    private const SEEDED_USER_EMAILS = [
        'employee.br@example.com',
        'employee.us@example.com',
        'employee.gb@example.com',
        'employee.jp@example.com',
        'employee.ca@example.com',
        'finance@example.com',
    ];

    public function run(): void
    {
        $users = User::query()
            ->whereIn('email', self::SEEDED_USER_EMAILS)
            ->get()
            ->keyBy('email');

        PaymentRequest::query()
            ->whereIn('user_id', $users->pluck('id')->all())
            ->delete();

        $creationService = new PaymentRequestCreationService(new SeedExchangeRateService(self::SEEDED_RATES));
        $stateService = new PaymentRequestStateService();
        $finance = $users->get('finance@example.com');
        $now = now();

        $creationService->create($users->get('employee.br@example.com'), '1100.00', 'BRL');

        $oldPending = $creationService->create($users->get('employee.us@example.com'), '550.00', 'USD');
        $oldPending->forceFill([
            'created_at' => $now->copy()->subHours(49),
            'updated_at' => $now->copy()->subHours(49),
        ])->save();

        $approved = $creationService->create($users->get('employee.gb@example.com'), '850.00', 'GBP');
        $stateService->approve($approved, $finance);

        $rejected = $creationService->create($users->get('employee.jp@example.com'), '170000.00', 'JPY');
        $stateService->reject($rejected, $finance);

        $expired = $creationService->create($users->get('employee.ca@example.com'), '1480.00', 'CAD');
        $expiredAt = $now->copy()->subHour();
        $expired->forceFill([
            'status' => PaymentRequestStatus::EXPIRED,
            'created_at' => $now->copy()->subHours(72),
            'expired_at' => $expiredAt,
            'updated_at' => $expiredAt,
        ])->save();
    }
}

class SeedExchangeRateService extends ExchangeRateService
{
    /**
     * @param  array<string, float>  $rates
     */
    public function __construct(
        private readonly array $rates,
    ) {
    }

    public function getEurTo(string $currency): RateDTO
    {
        $currency = strtoupper(trim($currency));

        if (! array_key_exists($currency, $this->rates)) {
            throw new InvalidArgumentException(sprintf('Missing seeded exchange rate for %s.', $currency));
        }

        return new RateDTO(
            rate: $this->rates[$currency],
            source: 'seed',
            fetchedAt: Carbon::now(),
        );
    }
}
