<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreGoogleDriveFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
            'name' => ['nullable', 'string', 'max:255'],
            'make_public' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File wajib diunggah.',
            'file.file' => 'Input file harus berupa file yang valid.',
            'name.string' => 'Nama file harus berupa teks.',
            'name.max' => 'Nama file maksimal 255 karakter.',
            'make_public.boolean' => 'Parameter make_public harus berupa true atau false.',
        ];
    }
}
