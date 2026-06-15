<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    public function index(CurrencyService $currencyService): JsonResponse
    {
        return response()->json([
            'data' => $currencyService->supportedCurrencies(),
        ]);
    }
}
