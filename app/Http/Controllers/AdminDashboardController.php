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

        $mitraCounts = ApplicantProfile::query()
            ->selectRaw('kode_kec_dom, kode_desa_dom, COUNT(*) as total')
            ->whereNotNull('kode_kec_dom')
            ->whereNotNull('kode_desa_dom')
            ->groupBy('kode_kec_dom', 'kode_desa_dom');

        $domisiliSummary = DB::table('master_kecamatan_desa as mkd')
            ->selectRaw('
                mkd.kode_kec as kode_kec_dom,
                mkd.nama_kec as nama_kec_dom,
                mkd.kode_desa as kode_desa_dom,
                mkd.nama_desa as nama_desa_dom,
                COALESCE(mc.total, 0) as total
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
                    'total' => $rows->sum('total'),
                    'desa' => $rows
                        ->map(static fn (object $row): array => [
                            'kode_desa_dom' => $row->kode_desa_dom,
                            'nama_desa_dom' => $row->nama_desa_dom,
                            'total' => (int) $row->total,
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
                'domisili_summary' => $domisiliSummary,
            ],
        ]);
    }
}
