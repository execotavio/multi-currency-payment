<?php

namespace App\Console\Commands;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use Illuminate\Console\Command;

class ExpirePendingPaymentRequests extends Command
{
    protected $signature = 'payments:expire-pending';

    protected $description = 'Expire pending payment requests older than 48 hours';

    public function handle(): int
    {
        $cutoff = now()->subHours(48);
        $timestamp = now();
        $expiredCount = 0;

        PaymentRequest::query()
            ->where('status', PaymentRequestStatus::PENDING->value)
            ->where('created_at', '<', $cutoff)
            ->select('id')
            ->chunkById(500, function ($paymentRequests) use (&$expiredCount, $timestamp): void {
                $expiredCount += PaymentRequest::query()
                    ->whereKey($paymentRequests->pluck('id')->all())
                    ->update([
                        'status' => PaymentRequestStatus::EXPIRED->value,
                        'expired_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
            });

        $this->info(sprintf('Expired %d pending payment requests.', $expiredCount));

        return self::SUCCESS;
    }
}
