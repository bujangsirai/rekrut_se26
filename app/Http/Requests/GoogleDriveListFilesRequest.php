<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class GoogleDriveListFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    public function rules(): array
    {
        return [
            'page_size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page_token' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'page_size.integer' => 'Parameter page_size harus berupa angka.',
            'page_size.min' => 'Parameter page_size minimal 1.',
            'page_size.max' => 'Parameter page_size maksimal 100.',
            'page_token.string' => 'Parameter page_token harus berupa string.',
        ];
    }
}
