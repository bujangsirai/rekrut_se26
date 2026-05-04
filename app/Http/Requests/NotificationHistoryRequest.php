<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class NotificationHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    public function rules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'limit.integer' => 'Parameter limit harus berupa angka.',
            'limit.min' => 'Parameter limit minimal 1.',
            'limit.max' => 'Parameter limit maksimal 100.',
        ];
    }
}
