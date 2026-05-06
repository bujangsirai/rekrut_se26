<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicRegistrationRequest;
use App\Models\ApplicantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicRegistrationController extends Controller
{
    public function create(): Response
    {
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

        return Inertia::render('PublicRegistrationPage', [
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
        ]);
    }

    public function store(PublicRegistrationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $ktpPath = $request->file('ktp_file')->store('mitra/ktp', 'local');
        unset($validated['ktp_file']);

        ApplicantProfile::query()->create([
            ...$validated,
            'url_ktp' => $ktpPath,
            'kode_akses' => Str::uuid()->toString(),
            'kode_akses_kedaluwarsa_pada' => now()->addDays(30),
            'status_wawancara' => 'Belum Wawancara',
            'status_kelulusan' => 'Belum Lulus',
        ]);

        return redirect()
            ->route('public.register')
            ->with('success', 'Pendaftaran berhasil dikirim. Tim kami akan menghubungi Anda.');
    }

    public function checkStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'nik' => ['required', 'string', 'digits:16'],
        ]);

        $mitra = ApplicantProfile::query()->where('nik', $request->nik)->first();

        if (! $mitra) {
            return redirect()
                ->back()
                ->withErrors(['nik' => 'NIK yang anda masukkan belum terdaftar, silahkan cek kembali NIK anda atau mendaftar terlebih dahulu']);
        }

        return redirect()->route('public.status', ['nik' => $request->nik]);
    }

    public function showStatus(string $nik): Response
    {
        $mitra = ApplicantProfile::query()->where('nik', $nik)->firstOrFail();

        return Inertia::render('PublicStatusPage', [
            'mitra' => $mitra,
        ]);
    }
}
