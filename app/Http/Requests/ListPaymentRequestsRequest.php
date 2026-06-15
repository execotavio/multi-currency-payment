<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListPaymentRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:pending,approved,rejected,expired'],
        ];
    }
}
