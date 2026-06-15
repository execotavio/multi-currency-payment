<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentRequestResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'amount_local' => $this->amount_local,
            'currency' => $this->currency,
            'amount_eur' => $this->amount_eur,
            'status' => $this->status->value,
            'eur_to_local_rate' => $this->eur_to_local_rate,
            'rate_source' => $this->rate_source,
            'rate_fetched_at' => $this->rate_fetched_at?->toISOString(),
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'expired_at' => $this->expired_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
