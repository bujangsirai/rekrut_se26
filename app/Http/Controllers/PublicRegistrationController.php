<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicRegistrationRequest;
use App\Models\ApplicantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $spekHpPath = $request->file('spek_hp_file')->store('mitra/spek_hp', 'local');
        $followIgPath = $request->file('follow_ig_file')->store('mitra/follow_ig', 'local');

        unset($validated['ktp_file'], $validated['spek_hp_file'], $validated['follow_ig_file']);

        DB::transaction(function () use ($validated, $ktpPath, $spekHpPath, $followIgPath) {
            ApplicantProfile::query()->create([
                ...$validated,
                'kode_akses' => Str::uuid()->toString(),
                'kode_akses_kedaluwarsa_pada' => now()->addMonths(3),
                'status_wawancara' => 'Belum Wawancara',
                'status_kelulusan' => 'Belum Lulus',
            ]);

            DB::table('mitra_berkas')->insert([
                [
                    'nik' => $validated['nik'],
                    'jenis_berkas' => 'ktp',
                    'file_path' => $ktpPath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nik' => $validated['nik'],
                    'jenis_berkas' => 'spek_hp',
                    'file_path' => $spekHpPath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nik' => $validated['nik'],
                    'jenis_berkas' => 'follow_ig',
                    'file_path' => $followIgPath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });

        return redirect()
            ->route('public.register.success')
            ->with([
                'success' => true,
                'nik' => $validated['nik'],
                'nama_lengkap' => $validated['nama_lengkap'],
            ]);
    }

    public function success(): Response|RedirectResponse
    {
        $nik = session('nik');

        if (! $nik) {
            return redirect()->route('public.register');
        }

        $requestMitra = ApplicantProfile::query()->where('nik', $nik)->first();
        if (! $requestMitra) {
            return redirect()->route('public.register');
        }

        session()->put('status_nik', $requestMitra->nik);

        $uploadSobatExists = DB::table('mitra_berkas')
            ->where('nik', $requestMitra->nik)
            ->where('jenis_berkas', 'upload_sobat')
            ->exists();

        return Inertia::render('PublicStatusPage', [
            'mitra' => $requestMitra,
            'uploadSobatExists' => $uploadSobatExists,
        ]);
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
                ->withErrors(['nik' => 'NIK yang anda masukkan belum terdaftar, silahkan cek kembali NIK anda atau mendaftar terlebih dahulu'])
                ->with('error', 'NIK tidak terdaftar.');
        }

        $request->session()->put('status_nik', $mitra->nik);

        return redirect()
            ->route('public.status');
    }

    public function showStatus(): Response|RedirectResponse
    {
        $nik = session('status_nik');

        if (! $nik) {
            return redirect()->route('home');
        }

        $mitra = ApplicantProfile::query()->where('nik', $nik)->firstOrFail();
        $uploadSobatExists = DB::table('mitra_berkas')
            ->where('nik', $mitra->nik)
            ->where('jenis_berkas', 'upload_sobat')
            ->exists();

        return Inertia::render('PublicStatusPage', [
            'mitra' => $mitra,
            'uploadSobatExists' => $uploadSobatExists,
        ]);
    }

    public function uploadSobat(Request $request): RedirectResponse
    {
        $nik = session('status_nik');

        if (! $nik) {
            return redirect()
                ->route('home')
                ->with('error', 'Sesi status telah berakhir. Silahkan cek status kembali.');
        }

        $mitra = ApplicantProfile::query()->where('nik', $nik)->first();
        if (! $mitra) {
            return redirect()
                ->route('home')
                ->with('error', 'Data pendaftar tidak ditemukan.');
        }

        $validated = $request->validate([
            'upload_sobat_file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ]);

        $filePath = $validated['upload_sobat_file']->store('mitra/upload_sobat', 'local');

        $existingUpload = DB::table('mitra_berkas')
            ->select(['id', 'file_path'])
            ->where('nik', $mitra->nik)
            ->where('jenis_berkas', 'upload_sobat')
            ->first();

        DB::transaction(function () use ($existingUpload, $mitra, $filePath): void {
            if ($existingUpload) {
                DB::table('mitra_berkas')
                    ->where('id', $existingUpload->id)
                    ->update([
                        'file_path' => $filePath,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('mitra_berkas')->insert([
                    'nik' => $mitra->nik,
                    'jenis_berkas' => 'upload_sobat',
                    'file_path' => $filePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $mitra->update([
                'status_sobat' => 'Sudah',
            ]);
        });

        if ($existingUpload?->file_path && Storage::disk('local')->exists($existingUpload->file_path)) {
            Storage::disk('local')->delete($existingUpload->file_path);
        }

        $request->session()->put('status_nik', $mitra->nik);

        return redirect()
            ->route('public.status')
            ->with('success', 'Bukti pendaftaran berhasil diunggah.');
    }

    public function selection(string $kode_akses): Response|RedirectResponse
    {
        $mitra = ApplicantProfile::query()
            ->where('kode_akses', $kode_akses)
            ->first();

        if (! $mitra) {
            abort(404, 'Kode akses tidak valid.');
        }

        if ($mitra->kode_akses_kedaluwarsa_pada && $mitra->kode_akses_kedaluwarsa_pada->isPast()) {
            abort(403, 'Kode akses telah kedaluwarsa.');
        }

        $mitra->update([
            'terakhir_diakses_pada' => now(),
        ]);

        // TODO : DO NOT CHANGE THIS LINE AND COMMENT
        $mulaiWawancara = true;

        if (! (bool) $mulaiWawancara) {
            return Inertia::render('PublicStatusPage', [
                'mitra' => $mitra,
            ]);
        }

        return Inertia::render('SelectionStatusPage', [
            'mitra' => $mitra,
        ]);
    }
}
