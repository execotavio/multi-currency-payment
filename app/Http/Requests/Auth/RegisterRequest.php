<?php

namespace App\Http\Requests\Auth;

use App\Services\CountryService;
use App\Services\CurrencyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'country' => [
                'required',
                'string',
                'size:2',
                'regex:/^[A-Z]{2}$/',
                Rule::in(app(CountryService::class)->supportedCodes()),
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
                'regex:/^[A-Z]{3}$/',
                Rule::in(app(CurrencyService::class)->supportedCodes()),
            ],
            'role' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('country')) {
            $this->merge([
                'country' => strtoupper(trim((string) $this->input('country'))),
            ]);
        }

        if ($this->has('currency')) {
            $this->merge([
                'currency' => strtoupper(trim((string) $this->input('currency'))),
            ]);
        }
    }
}
