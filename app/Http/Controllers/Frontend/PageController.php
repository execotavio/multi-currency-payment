<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function register(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function paymentRequestsIndex(): Response
    {
        return Inertia::render('PaymentRequests/Index');
    }

    public function paymentRequestsCreate(): Response
    {
        return Inertia::render('PaymentRequests/Create');
    }

    public function paymentRequestsShow(string $paymentRequest): Response
    {
        return Inertia::render('PaymentRequests/Show', [
            'paymentRequestId' => $paymentRequest,
        ]);
    }
}
