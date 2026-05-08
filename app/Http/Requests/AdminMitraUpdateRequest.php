<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminMitraUpdateRequest extends FormRequest
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
        $mitraId = $this->route('mitra')?->id ?? $this->route('mitra');

        return [
            'nik' => ['required', 'string', 'size:16', Rule::unique('mitra', 'nik')->ignore($mitraId)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('mitra', 'email')->ignore($mitraId)],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'kode_kec' => ['required', 'string', 'max:7'],
            'kode_desa' => ['required', 'string', 'max:10'],
            'alamat_lengkap' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'status_perkawinan' => ['required', Rule::in(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])],
            'pendidikan_terakhir' => ['required', Rule::in(['SLTP/Kebawah', 'SLTA', 'DI/DII/DII', 'DIV/S1/S2/S3'])],
            'pekerjaan' => ['required', 'string', 'max:120'],
            'nomor_whatsapp' => ['required', 'string', 'max:30'],
            'riwayat_kegiatan_bps' => ['nullable', 'string'],
            'is_domksb' => ['required', 'boolean'],
            'is_motor' => ['required', 'boolean'],
            'is_not_asn' => ['required', 'boolean'],
            'is_not_hamil' => ['required', 'boolean'],
            'merk_hp' => ['required', 'string', 'max:255'],
            'kode_kec_dom' => ['nullable', 'string', 'max:7'],
            'kode_desa_dom' => ['nullable', 'string', 'max:10'],
            'status_sobat' => ['required', Rule::in(['Sudah', 'Belum'])],
            'status_wawancara' => ['required', Rule::in(['Belum Wawancara', 'Sudah Wawancara'])],
            'status_kelulusan' => ['required', Rule::in(['Lulus', 'Belum Lulus'])],
        ];
    }
}
