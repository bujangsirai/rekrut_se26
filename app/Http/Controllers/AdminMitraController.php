<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminMitraStoreRequest;
use App\Http\Requests\AdminMitraUpdateRequest;
use App\Models\ApplicantProfile;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminMitraController extends Controller
{
    public function index(Request $request): Response
    {
        [$mitra, $kecamatanOptions, $desaOptions] = $this->buildMitraPageData(false);

        return Inertia::render('AdminMitraPage', [
            'mitra' => $mitra,
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
        ]);
    }

    public function seleksiMitra(Request $request): Response
    {
        [$mitra, $kecamatanOptions, $desaOptions] = $this->buildMitraPageData(true);

        return Inertia::render('AdminSeleksiMitraPage', [
            'mitra' => $mitra,
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
        ]);
    }

    /**
     * @return array{0: Collection<int, array<string, mixed>>, 1: Collection<int, array{kode_kec: string, nama_kec: string}>, 2: Collection<int, array{kode_kec: string, kode_desa: string, nama_desa: string}>}
     */
    private function buildMitraPageData(bool $onlySobatSudah): array
    {
        $mitraQuery = ApplicantProfile::query()
            ->select([
                'mitra.*',
                'mkd_ktp.nama_kec as nama_kec_ktp',
                'mkd_ktp.nama_desa as nama_desa_ktp',
                'mkd_dom.nama_kec as nama_kec_dom',
                'mkd_dom.nama_desa as nama_desa_dom',
                'mb_ktp.file_path as ktp_path',
                'mb_spek.file_path as spek_hp_path',
                'mb_ig.file_path as follow_ig_path',
                'mb_sobat.file_path as upload_sobat_path',
            ])
            ->leftJoin('master_kecamatan_desa as mkd_ktp', function ($join) {
                $join->on('mitra.kode_kec', '=', 'mkd_ktp.kode_kec')
                    ->on('mitra.kode_desa', '=', 'mkd_ktp.kode_desa');
            }, null, null)
            ->leftJoin('master_kecamatan_desa as mkd_dom', function ($join) {
                $join->on('mitra.kode_kec_dom', '=', 'mkd_dom.kode_kec')
                    ->on('mitra.kode_desa_dom', '=', 'mkd_dom.kode_desa');
            }, null, null)
            ->leftJoin('mitra_berkas as mb_ktp', function ($join) {
                $join->on('mitra.nik', '=', 'mb_ktp.nik')
                    ->where('mb_ktp.jenis_berkas', '=', 'ktp');
            }, null, null)
            ->leftJoin('mitra_berkas as mb_spek', function ($join) {
                $join->on('mitra.nik', '=', 'mb_spek.nik')
                    ->where('mb_spek.jenis_berkas', '=', 'spek_hp');
            }, null, null)
            ->leftJoin('mitra_berkas as mb_ig', function ($join) {
                $join->on('mitra.nik', '=', 'mb_ig.nik')
                    ->where('mb_ig.jenis_berkas', '=', 'follow_ig');
            }, null, null)
            ->leftJoin('mitra_berkas as mb_sobat', function ($join) {
                $join->on('mitra.nik', '=', 'mb_sobat.nik')
                    ->where('mb_sobat.jenis_berkas', '=', 'upload_sobat');
            }, null, null);

        if ($onlySobatSudah) {
            $mitraQuery->where('mitra.status_sobat', 'Sudah');
        }

        $mitra = $mitraQuery
            ->latest('mitra.id')
            ->get()
            ->map(static fn (object $item): array => [
                'id' => $item->id,
                'nik' => $item->nik,
                'url_ktp' => $item->ktp_path ? Crypt::encryptString($item->ktp_path) : null,
                'url_spek_hp' => $item->spek_hp_path ? Crypt::encryptString($item->spek_hp_path) : null,
                'url_follow_ig' => $item->follow_ig_path ? Crypt::encryptString($item->follow_ig_path) : null,
                'url_upload_sobat' => $item->upload_sobat_path ? Crypt::encryptString($item->upload_sobat_path) : null,
                'kode_akses' => $item->kode_akses,
                'nama_lengkap' => $item->nama_lengkap,
                'email' => $item->email,
                'jenis_kelamin' => $item->jenis_kelamin,
                'kode_kec' => $item->kode_kec,
                'nama_kec' => $item->nama_kec_dom ?? $item->nama_kec_ktp,
                'kode_desa' => $item->kode_desa,
                'nama_desa' => $item->nama_desa_dom ?? $item->nama_desa_ktp,
                'nomor_whatsapp' => $item->nomor_whatsapp,
                'status_sobat' => $item->status_sobat,
                'is_mitrakepka' => (bool) $item->is_mitrakepka,
                'status_wawancara' => $item->status_wawancara,
                'status_kelulusan' => $item->status_kelulusan,
                'jawaban_kuesioner' => $item->jawaban_kuesioner,
                'skor_kuesioner' => $item->skor_kuesioner,
                'tanggal_lahir' => Carbon::parse($item->tanggal_lahir)->format('Y-m-d'),
                'tempat_lahir' => $item->tempat_lahir,
                'status_perkawinan' => $item->status_perkawinan,
                'pendidikan_terakhir' => $item->pendidikan_terakhir,
                'pekerjaan' => $item->pekerjaan,
                'alamat_lengkap' => $item->alamat_lengkap,
                'riwayat_kegiatan_bps' => $item->riwayat_kegiatan_bps,
                'is_domksb' => (bool) $item->is_domksb,
                'is_motor' => (bool) $item->is_motor,
                'is_not_asn' => (bool) $item->is_not_asn,
                'is_not_hamil' => (bool) $item->is_not_hamil,
                'merk_hp' => $item->merk_hp,
                'kode_kec_dom' => $item->kode_kec_dom,
                'kode_desa_dom' => $item->kode_desa_dom,
                'created_at' => $item->created_at ? Carbon::parse($item->created_at)->toDateTimeString() : null,
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

        return [$mitra, $kecamatanOptions, $desaOptions];
    }

    public function export()
    {
        $mitra = ApplicantProfile::query()
            ->select([
                'mitra.*',
                'mkd_ktp.nama_kec as nama_kec_ktp',
                'mkd_ktp.nama_desa as nama_desa_ktp',
                'mkd_dom.nama_kec as nama_kec_dom',
                'mkd_dom.nama_desa as nama_desa_dom',
            ])
            ->leftJoin('master_kecamatan_desa as mkd_ktp', function ($join) {
                $join->on('mitra.kode_kec', '=', 'mkd_ktp.kode_kec')
                    ->on('mitra.kode_desa', '=', 'mkd_ktp.kode_desa');
            }, null, null)
            ->leftJoin('master_kecamatan_desa as mkd_dom', function ($join) {
                $join->on('mitra.kode_kec_dom', '=', 'mkd_dom.kode_kec')
                    ->on('mitra.kode_desa_dom', '=', 'mkd_dom.kode_desa');
            }, null, null)
            ->latest('mitra.id')
            ->get();

        $filename = 'data_mitra_'.date('Y-m-d_H-i-s').'.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'NIK', 'Nama Lengkap', 'Email', 'Jenis Kelamin',
            'Kecamatan KTP', 'Desa KTP', 'Alamat KTP',
            'Domisili KSB', 'Kecamatan Domisili', 'Desa Domisili',
            'Tempat Lahir', 'Tanggal Lahir', 'Status Perkawinan',
            'Pendidikan', 'Pekerjaan', 'Nomor WA', 'Merk HP',
            'Bukan ASN', 'Tidak Hamil', 'Memiliki Motor', 'is_mitrakepka',
            'Status Sobat', 'Status Wawancara', 'Status Kelulusan',
            'Riwayat Kegiatan BPS', 'Tanggal Daftar',
        ];

        $callback = function () use ($mitra, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($mitra as $item) {
                fputcsv($file, array_map([self::class, 'sanitizeCsvValue'], [
                    $item->nik,
                    $item->nama_lengkap,
                    $item->email,
                    $item->jenis_kelamin,
                    $item->nama_kec_ktp,
                    $item->nama_desa_ktp,
                    $item->alamat_lengkap,
                    $item->is_domksb ? 'Ya' : 'Tidak',
                    $item->nama_kec_dom ?? '-',
                    $item->nama_desa_dom ?? '-',
                    $item->tempat_lahir,
                    $item->tanggal_lahir?->format('Y-m-d'),
                    $item->status_perkawinan,
                    $item->pendidikan_terakhir,
                    $item->pekerjaan,
                    $item->nomor_whatsapp,
                    $item->merk_hp,
                    $item->is_not_asn ? 'Ya' : 'Tidak',
                    $item->is_not_hamil ? 'Ya' : 'Tidak',
                    $item->is_motor ? 'Ya' : 'Tidak',
                    $item->is_mitrakepka ? 'Ya' : 'Tidak',
                    $item->status_sobat,
                    $item->status_wawancara,
                    $item->status_kelulusan,
                    $item->riwayat_kegiatan_bps,
                    $item->created_at?->toDateTimeString(),
                ]));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

    public function file(string $payload)
    {
        try {
            $path = Crypt::decryptString($payload);
        } catch (DecryptException $e) {
            abort(404, 'Link file tidak valid.');
        }

        if (! Storage::disk('local')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file(Storage::disk('local')->path($path));
    }

    private static function sanitizeCsvValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        // Normalize multiline input so CSV rows stay intact in spreadsheet apps.
        return preg_replace('/\s*\R\s*/u', ' ', $value);
    }
}
