<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => ['required', 'string', 'digits:16', 'unique:mitra,nik'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'ktp_file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'kode_kec' => ['required', 'string', 'max:7', 'exists:master_kecamatan_desa,kode_kec'],
            'kode_desa' => ['required', 'string', 'max:10', 'exists:master_kecamatan_desa,kode_desa'],
            'alamat_lengkap' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'status_perkawinan' => ['required', Rule::in(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])],
            'pendidikan_terakhir' => ['required', Rule::in(['SLTP/Kebawah', 'SLTA', 'DI/DII/DII', 'DIV/S1/S2/S3'])],
            'pekerjaan' => ['required', 'string', 'max:120'],
            'nomor_whatsapp' => ['required', 'string', 'max:30'],
            'riwayat_kegiatan_bps' => ['nullable', 'string'],
        ];
    }
}
