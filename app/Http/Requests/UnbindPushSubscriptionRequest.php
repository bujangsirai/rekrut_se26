<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnbindPushSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'endpoint' => ['required', 'string', 'max:500'],
            'public_key' => ['nullable', 'string'],
            'auth_token' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'endpoint.required' => 'Endpoint subscription wajib diisi.',
            'endpoint.string' => 'Endpoint subscription harus berupa string.',
            'endpoint.max' => 'Endpoint subscription terlalu panjang.',
            'public_key.string' => 'Public key subscription harus berupa string.',
            'auth_token.string' => 'Auth token subscription harus berupa string.',
        ];
    }
}
