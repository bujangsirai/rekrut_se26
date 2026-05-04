<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantProfile extends Model
{
    use HasFactory;

    protected $table = 'mitra';

    protected $fillable = [
        'posisi_dilamar',
        'nama_lengkap',
        'email',
        'url_ktp',
        'kode_akses',
        'kode_akses_kedaluwarsa_pada',
        'terakhir_diakses_pada',
        'jenis_kelamin',
        'kode_kec',
        'kode_desa',
        'alamat_lengkap',
        'tanggal_lahir',
        'tempat_lahir',
        'status_perkawinan',
        'pendidikan_terakhir',
        'pekerjaan',
        'nomor_whatsapp',
        'riwayat_kegiatan_bps',
        'status_sobat',
        'status_wawancara',
        'status_kelulusan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'kode_akses_kedaluwarsa_pada' => 'datetime',
            'terakhir_diakses_pada' => 'datetime',
        ];
    }
}
