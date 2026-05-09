<?php

namespace App\Http\Controllers;

use App\Models\ApplicantProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $totalPendaftar = ApplicantProfile::query()->count();
        $totalMitraKepka = ApplicantProfile::query()
            ->where('is_mitrakepka', true)
            ->count();
        $totalMitraLulus = ApplicantProfile::query()
            ->where('status_kelulusan', 'Lulus')
            ->count();

        $mitraCounts = ApplicantProfile::query()
            ->selectRaw("
                kode_kec_dom,
                kode_desa_dom,
                COUNT(*) as total_pendaftar,
                SUM(CASE WHEN is_mitrakepka = 1 THEN 1 ELSE 0 END) as total_mitra_kepka,
                SUM(CASE WHEN status_kelulusan = 'Lulus' THEN 1 ELSE 0 END) as total_mitra_lulus
            ")
            ->whereNotNull('kode_kec_dom')
            ->whereNotNull('kode_desa_dom')
            ->groupBy('kode_kec_dom', 'kode_desa_dom');

        $domisiliSummary = DB::table('master_kecamatan_desa as mkd')
            ->selectRaw('
                mkd.kode_kec as kode_kec_dom,
                mkd.nama_kec as nama_kec_dom,
                mkd.kode_desa as kode_desa_dom,
                mkd.nama_desa as nama_desa_dom,
                COALESCE(mc.total_pendaftar, 0) as total_pendaftar,
                COALESCE(mc.total_mitra_kepka, 0) as total_mitra_kepka,
                COALESCE(mc.total_mitra_lulus, 0) as total_mitra_lulus
            ')
            ->leftJoinSub($mitraCounts, 'mc', function ($join) {
                $join->on('mkd.kode_kec', '=', 'mc.kode_kec_dom')
                    ->on('mkd.kode_desa', '=', 'mc.kode_desa_dom');
            })
            ->orderBy('mkd.kode_kec')
            ->orderBy('mkd.kode_desa')
            ->get()
            ->groupBy(static function (object $row): string {
                return sprintf('%s|%s', $row->kode_kec_dom, $row->nama_kec_dom);
            })
            ->map(static function ($rows): array {
                $firstRow = $rows->first();

                return [
                    'kode_kec_dom' => $firstRow?->kode_kec_dom,
                    'nama_kec_dom' => $firstRow?->nama_kec_dom,
                    'total_pendaftar' => (int) $rows->sum('total_pendaftar'),
                    'total_mitra_kepka' => (int) $rows->sum('total_mitra_kepka'),
                    'total_mitra_lulus' => (int) $rows->sum('total_mitra_lulus'),
                    'desa' => $rows
                        ->map(static fn (object $row): array => [
                            'kode_desa_dom' => $row->kode_desa_dom,
                            'nama_desa_dom' => $row->nama_desa_dom,
                            'total_pendaftar' => (int) $row->total_pendaftar,
                            'total_mitra_kepka' => (int) $row->total_mitra_kepka,
                            'total_mitra_lulus' => (int) $row->total_mitra_lulus,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('AdminDashboardPage', [
            'user' => [
                'id' => $request->user()?->id,
                'username' => $request->user()?->username,
            ],
            'dashboardStats' => [
                'total_pendaftar' => $totalPendaftar,
                'total_mitra_kepka' => $totalMitraKepka,
                'total_mitra_lulus' => $totalMitraLulus,
                'domisili_summary' => $domisiliSummary,
            ],
        ]);
    }
}
