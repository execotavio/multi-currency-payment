<?php

namespace App\Http\Controllers;

use App\Services\CountryService;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function index(CountryService $countryService): JsonResponse
    {
        return response()->json([
            'data' => $countryService->supportedCountries(),
        ]);
    }
}
