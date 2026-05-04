<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminMitraStoreRequest;
use App\Http\Requests\AdminMitraUpdateRequest;
use App\Models\ApplicantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminMitraController extends Controller
{
    public function index(Request $request): Response
    {
        $mitra = ApplicantProfile::query()
            ->select([
                'id',
                'nama_lengkap',
                'email',
                'jenis_kelamin',
                'kode_kec',
                'kode_desa',
                'nomor_whatsapp',
                'status_sobat',
                'status_wawancara',
                'status_kelulusan',
                'tanggal_lahir',
                'tempat_lahir',
                'status_perkawinan',
                'pendidikan_terakhir',
                'pekerjaan',
                'alamat_lengkap',
                'riwayat_kegiatan_bps',
                'created_at',
            ])
            ->latest('id')
            ->get()
            ->map(static fn (ApplicantProfile $item): array => [
                'id' => $item->id,
                'nama_lengkap' => $item->nama_lengkap,
                'email' => $item->email,
                'jenis_kelamin' => $item->jenis_kelamin,
                'kode_kec' => $item->kode_kec,
                'kode_desa' => $item->kode_desa,
                'nomor_whatsapp' => $item->nomor_whatsapp,
                'status_sobat' => $item->status_sobat,
                'status_wawancara' => $item->status_wawancara,
                'status_kelulusan' => $item->status_kelulusan,
                'tanggal_lahir' => $item->tanggal_lahir?->format('Y-m-d'),
                'tempat_lahir' => $item->tempat_lahir,
                'status_perkawinan' => $item->status_perkawinan,
                'pendidikan_terakhir' => $item->pendidikan_terakhir,
                'pekerjaan' => $item->pekerjaan,
                'alamat_lengkap' => $item->alamat_lengkap,
                'riwayat_kegiatan_bps' => $item->riwayat_kegiatan_bps,
                'created_at' => $item->created_at?->toDateTimeString(),
            ])
            ->values();

        return Inertia::render('AdminMitraPage', [
            'mitra' => $mitra,
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
        $mitra->delete();

        return redirect()
            ->route('admin.mitra.index')
            ->with('success', 'Data mitra berhasil dihapus.');
    }
}
