<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGoogleDriveFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'make_public' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama file wajib diisi.',
            'name.string' => 'Nama file harus berupa teks.',
            'name.max' => 'Nama file maksimal 255 karakter.',
            'make_public.boolean' => 'Parameter make_public harus berupa true atau false.',
        ];
    }
}
