<?php

namespace App\Services;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;

class PaymentRequestStateService
{
    /**
     * @var array<int, string>
     */
    private const IMMUTABLE_FIELDS = [
        'amount_eur',
        'eur_to_local_rate',
        'rate_source',
        'rate_fetched_at',
    ];

    public function approve(PaymentRequest $paymentRequest, User $reviewer): void
    {
        $this->assertFinanceReviewer($reviewer, 'approve');
        $this->assertTransitionAllowed($paymentRequest, 'approve');

        $paymentRequest->forceFill([
            'status' => PaymentRequestStatus::APPROVED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ])->save();
    }

    public function reject(PaymentRequest $paymentRequest, User $reviewer): void
    {
        $this->assertFinanceReviewer($reviewer, 'reject');
        $this->assertTransitionAllowed($paymentRequest, 'reject');

        $paymentRequest->forceFill([
            'status' => PaymentRequestStatus::REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ])->save();
    }

    public function expire(PaymentRequest $paymentRequest): void
    {
        $this->assertTransitionAllowed($paymentRequest, 'expire');

        $paymentRequest->forceFill([
            'status' => PaymentRequestStatus::EXPIRED,
            'expired_at' => now(),
        ])->save();
    }

    public function assertImmutableFieldsUnchanged(PaymentRequest $paymentRequest, array $attributes): void
    {
        $immutableChanges = array_intersect_key($attributes, array_flip(self::IMMUTABLE_FIELDS));

        if ($immutableChanges !== []) {
            $field = array_key_first($immutableChanges);
            throw new DomainException(sprintf('Cannot modify immutable field: %s', $field));
        }
    }

    public function applyImmutableGuard(PaymentRequest $paymentRequest, array $attributes): void
    {
        $this->assertImmutableFieldsUnchanged($paymentRequest, $attributes);
    }

    private function assertFinanceReviewer(User $reviewer, string $action): void
    {
        if (! $reviewer->isFinance()) {
            throw new DomainException(sprintf('Only finance users can %s payment requests', $action));
        }
    }

    private function assertTransitionAllowed(PaymentRequest $paymentRequest, string $action): void
    {
        if (! $paymentRequest->isPending()) {
            throw new DomainException(sprintf('Only pending payment requests can be %s', $action));
        }
    }
}
