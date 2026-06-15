<?php

namespace Tests\Feature\Seeders;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_demo_users_and_payment_requests(): void
    {
        $this->artisan('db:seed')->assertExitCode(0);

        $this->assertGreaterThanOrEqual(5, User::query()->where('role', 'employee')->count());
        $this->assertGreaterThanOrEqual(1, User::query()->where('role', 'finance')->count());

        $employeeCurrencies = User::query()
            ->where('role', 'employee')
            ->pluck('currency')
            ->unique();

        $this->assertGreaterThanOrEqual(5, $employeeCurrencies->count());
        $this->assertGreaterThan(0, PaymentRequest::query()->count());

        foreach ([
            PaymentRequestStatus::PENDING,
            PaymentRequestStatus::APPROVED,
            PaymentRequestStatus::REJECTED,
            PaymentRequestStatus::EXPIRED,
        ] as $status) {
            $this->assertDatabaseHas('payment_requests', [
                'status' => $status->value,
                'rate_source' => 'seed',
            ]);
        }

        PaymentRequest::query()
            ->whereIn('status', [
                PaymentRequestStatus::APPROVED->value,
                PaymentRequestStatus::REJECTED->value,
            ])
            ->get()
            ->each(function (PaymentRequest $paymentRequest): void {
                $this->assertNotNull($paymentRequest->reviewed_by);
                $this->assertNotNull($paymentRequest->reviewed_at);
            });

        PaymentRequest::query()
            ->where('status', PaymentRequestStatus::EXPIRED->value)
            ->get()
            ->each(function (PaymentRequest $paymentRequest): void {
                $this->assertNotNull($paymentRequest->expired_at);
            });

        $userCount = User::query()->count();
        $paymentRequestCount = PaymentRequest::query()->count();

        $this->artisan('db:seed')->assertExitCode(0);

        $this->assertSame($userCount, User::query()->count());
        $this->assertSame($paymentRequestCount, PaymentRequest::query()->count());
    }
}
