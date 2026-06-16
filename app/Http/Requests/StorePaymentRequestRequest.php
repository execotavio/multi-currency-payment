<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_local' => ['required', 'numeric', 'gt:0'],
            'currency' => ['prohibited'],
        ];
    }
}
