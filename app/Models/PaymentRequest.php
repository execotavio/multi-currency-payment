<?php

namespace App\Models;

use App\Enums\PaymentRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    use HasFactory;

    protected const IMMUTABLE_FIELDS = [
        'amount_eur',
        'eur_to_local_rate',
        'rate_source',
        'rate_fetched_at',
    ];

    protected $fillable = [
        'user_id',
        'amount_local',
        'currency',
        'amount_eur',
        'eur_to_local_rate',
        'rate_source',
        'rate_fetched_at',
        'status',
        'reviewed_by',
        'reviewed_at',
        'expired_at',
    ];

    protected $casts = [
        'amount_local' => 'decimal:2',
        'amount_eur' => 'decimal:2',
        'eur_to_local_rate' => 'decimal:6',
        'rate_fetched_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'expired_at' => 'datetime',
        'status' => PaymentRequestStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === PaymentRequestStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === PaymentRequestStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === PaymentRequestStatus::REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->status === PaymentRequestStatus::EXPIRED;
    }

    public function fill(array $attributes): static
    {
        $immutableChanges = array_intersect_key($attributes, array_flip(self::IMMUTABLE_FIELDS));

        if ($immutableChanges !== [] && $this->exists) {
            $field = array_key_first($immutableChanges);
            throw new \DomainException(sprintf('Cannot modify immutable field: %s', $field));
        }

        return parent::fill($attributes);
    }
}
