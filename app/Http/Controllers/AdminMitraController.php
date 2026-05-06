<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminMitraStoreRequest;
use App\Http\Requests\AdminMitraUpdateRequest;
use App\Models\ApplicantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminMitraController extends Controller
{
    public function index(Request $request): Response
    {
        $mitra = ApplicantProfile::query()
            ->select([
                'mitra.id',
                'mitra.nik',
                'mitra.url_ktp',
                'mitra.kode_akses',
                'mitra.nama_lengkap',
                'mitra.email',
                'mitra.jenis_kelamin',
                'mitra.kode_kec',
                'mitra.kode_desa',
                'mitra.nomor_whatsapp',
                'mitra.status_sobat',
                'mitra.status_wawancara',
                'mitra.status_kelulusan',
                'mitra.tanggal_lahir',
                'mitra.tempat_lahir',
                'mitra.status_perkawinan',
                'mitra.pendidikan_terakhir',
                'mitra.pekerjaan',
                'mitra.alamat_lengkap',
                'mitra.riwayat_kegiatan_bps',
                'mitra.created_at',
                'mkd.nama_kec',
                'mkd.nama_desa',
            ])
            ->leftJoin('master_kecamatan_desa as mkd', function ($join) {
                $join->on('mitra.kode_kec', '=', 'mkd.kode_kec')
                     ->on('mitra.kode_desa', '=', 'mkd.kode_desa');
            }, null, null)
            ->latest('mitra.id')
            ->get()
            ->map(static fn (object $item): array => [
                'id' => $item->id,
                'nik' => $item->nik,
                'url_ktp' => $item->url_ktp ? \Illuminate\Support\Facades\Crypt::encryptString($item->url_ktp) : null,
                'kode_akses' => $item->kode_akses,
                'nama_lengkap' => $item->nama_lengkap,
                'email' => $item->email,
                'jenis_kelamin' => $item->jenis_kelamin,
                'kode_kec' => $item->kode_kec,
                'nama_kec' => $item->nama_kec,
                'kode_desa' => $item->kode_desa,
                'nama_desa' => $item->nama_desa,
                'nomor_whatsapp' => $item->nomor_whatsapp,
                'status_sobat' => $item->status_sobat,
                'status_wawancara' => $item->status_wawancara,
                'status_kelulusan' => $item->status_kelulusan,
                // Pastikan Intelephense tidak protes karena mengira ini dipanggil dari stdClass
                'tanggal_lahir' => \Carbon\Carbon::parse($item->tanggal_lahir)->format('Y-m-d'),
                'tempat_lahir' => $item->tempat_lahir,
                'status_perkawinan' => $item->status_perkawinan,
                'pendidikan_terakhir' => $item->pendidikan_terakhir,
                'pekerjaan' => $item->pekerjaan,
                'alamat_lengkap' => $item->alamat_lengkap,
                'riwayat_kegiatan_bps' => $item->riwayat_kegiatan_bps,
                'created_at' => $item->created_at ? \Carbon\Carbon::parse($item->created_at)->toDateTimeString() : null,
            ])
            ->values();

        $kecamatanOptions = DB::table('master_kecamatan_desa')
            ->select('kode_kec', 'nama_kec')
            ->distinct()
            ->orderBy('kode_kec')
            ->get()
            ->map(static fn (object $row): array => [
                'kode_kec' => $row->kode_kec,
                'nama_kec' => $row->nama_kec,
            ])
            ->values();

        $desaOptions = DB::table('master_kecamatan_desa')
            ->select('kode_kec', 'kode_desa', 'nama_desa')
            ->orderBy('kode_desa')
            ->get()
            ->map(static fn (object $row): array => [
                'kode_kec' => $row->kode_kec,
                'kode_desa' => $row->kode_desa,
                'nama_desa' => $row->nama_desa,
            ])
            ->values();

        return Inertia::render('AdminMitraPage', [
            'mitra' => $mitra,
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
        ]);
    }

    public function store(AdminMitraStoreRequest $request): RedirectResponse
    {
        ApplicantProfile::query()->create([
            ...$request->validated(),
            'kode_akses' => Str::uuid()->toString(),
            'kode_akses_kedaluwarsa_pada' => now()->addDays(30),
        ]);

        return redirect()
            ->route('admin.mitra.index')
            ->with('success', 'Data mitra berhasil ditambahkan.');
    }

    public function update(AdminMitraUpdateRequest $request, ApplicantProfile $mitra): RedirectResponse
    {
        $mitra->update($request->validated());

        return redirect()
            ->route('admin.mitra.index')
            ->with('success', 'Data mitra berhasil diperbarui.');
    }

    public function destroy(ApplicantProfile $mitra): RedirectResponse
    {
        $mitra->deleteOrFail();

        return redirect()
            ->route('admin.mitra.index')
            ->with('success', 'Data mitra berhasil dihapus.');
    }

    public function file($payload)
    {
        try {
            $path = \Illuminate\Support\Facades\Crypt::decryptString($payload);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'Link file tidak valid.');
        }

        if (! Storage::disk('local')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('local')->response($path);
    }
}
