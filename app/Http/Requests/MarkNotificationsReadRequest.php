<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class MarkNotificationsReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    public function rules(): array
    {
        return [
            'ids' => ['nullable', 'array'],
            'ids.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.array' => 'Parameter ids harus berupa array.',
            'ids.*.string' => 'Setiap id notifikasi harus berupa string.',
        ];
    }
}
