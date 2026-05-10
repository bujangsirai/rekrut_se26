<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminKuesionerController extends Controller
{
    public function index(): Response
    {
        $kuesionerList = DB::table('mitra_kuesioner')
            ->select(['id', 'structure', 'status', 'created_at'])
            ->orderByDesc('id')
            ->get()
            ->map(static function (object $row): array {
                $decodedStructure = json_decode($row->structure, true);

                return [
                    'id' => (int) $row->id,
                    'status' => $row->status,
                    'structure' => is_array($decodedStructure) ? $decodedStructure : null,
                    'created_at' => $row->created_at,
                ];
            })
            ->values()
            ->all();

        $activeKuesioner = collect($kuesionerList)
            ->firstWhere('status', 'active');

        return Inertia::render('AdminKuesionerPage', [
            'kuesionerList' => $kuesionerList,
            'activeStructure' => $activeKuesioner['structure'] ?? null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'structure' => ['required', 'json'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        /** @var mixed $decodedStructure */
        $decodedStructure = json_decode($validated['structure'], true);
        if (! is_array($decodedStructure) || ! array_key_exists('questions', $decodedStructure) || ! is_array($decodedStructure['questions'])) {
            return back()->withErrors([
                'structure' => 'Struktur JSON tidak valid. Field "questions" harus berupa array.',
            ]);
        }

        foreach ($decodedStructure['questions'] as $index => $question) {
            if (! is_array($question) || ! array_key_exists('scoring', $question) || ! is_numeric($question['scoring'])) {
                return back()->withErrors([
                    'structure' => sprintf('Pertanyaan ke-%d wajib memiliki props "scoring" berupa angka.', $index + 1),
                ]);
            }
        }

        DB::transaction(function () use ($validated): void {
            if ($validated['status'] === 'active') {
                DB::table('mitra_kuesioner')->update(['status' => 'inactive']);
            }

            DB::table('mitra_kuesioner')->insert([
                'structure' => $validated['structure'],
                'status' => $validated['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.kuesioner.index')
            ->with('success', 'Struktur kuesioner berhasil disimpan.');
    }
}
