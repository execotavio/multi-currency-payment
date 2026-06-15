<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequestRequest;
use App\Services\PaymentRequestCreationService;
use Illuminate\Http\JsonResponse;

class PaymentRequestController extends Controller
{
    public function store(
        StorePaymentRequestRequest $request,
        PaymentRequestCreationService $paymentRequestCreationService,
    ): JsonResponse {
        $paymentRequest = $paymentRequestCreationService->create(
            $request->user(),
            $request->validated('amount_local'),
            $request->validated('currency'),
        );

        return response()->json([
            'id' => $paymentRequest->id,
            'amount_local' => $paymentRequest->amount_local,
            'currency' => $paymentRequest->currency,
            'amount_eur' => $paymentRequest->amount_eur,
            'status' => $paymentRequest->status->value,
            'eur_to_local_rate' => $paymentRequest->eur_to_local_rate,
            'rate_source' => $paymentRequest->rate_source,
            'rate_fetched_at' => $paymentRequest->rate_fetched_at?->toISOString(),
        ], 201);
    }
}
