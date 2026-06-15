<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListPaymentRequestsRequest;
use App\Http\Requests\StorePaymentRequestRequest;
use App\Http\Resources\PaymentRequestResource;
use App\Models\PaymentRequest;
use App\Services\PaymentRequestCreationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PaymentRequestController extends Controller
{
    public function index(ListPaymentRequestsRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', PaymentRequest::class);

        $user = $request->user();
        $query = PaymentRequest::query();

        if ($user->isEmployee()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->validated('status'));
        }

        return PaymentRequestResource::collection(
            $query->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get(),
        );
    }

    public function store(
        StorePaymentRequestRequest $request,
        PaymentRequestCreationService $paymentRequestCreationService,
    ): JsonResponse {
        $paymentRequest = $paymentRequestCreationService->create(
            $request->user(),
            $request->validated('amount_local'),
            $request->validated('currency'),
        );

        return (new PaymentRequestResource($paymentRequest))
            ->response()
            ->setStatusCode(201);
    }

    public function show(PaymentRequest $paymentRequest): PaymentRequestResource
    {
        Gate::authorize('view', $paymentRequest);

        return new PaymentRequestResource($paymentRequest);
    }
}
