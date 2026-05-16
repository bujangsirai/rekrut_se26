<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        return Inertia::render('AdminKuesionerPage', [
            'kuesionerList' => $kuesionerList,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'structure' => ['required', 'json'],
        ]);

        $structureError = $this->validateStructureContent($validated['structure']);
        if ($structureError !== null) {
            return back()->withErrors([
                'structure' => $structureError,
            ]);
        }

        DB::table('mitra_kuesioner')->insert([
            'structure' => $validated['structure'],
            'status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.kuesioner.index')
            ->with('success', 'Struktur kuesioner berhasil disimpan.');
    }

    public function update(Request $request, int $kuesioner): RedirectResponse
    {
        $validated = $request->validate([
            'structure' => ['required', 'json'],
        ]);

        $structureError = $this->validateStructureContent($validated['structure']);
        if ($structureError !== null) {
            return back()->withErrors([
                'structure' => $structureError,
            ]);
        }

        DB::table('mitra_kuesioner')
            ->where('id', $kuesioner)
            ->update([
                'structure' => $validated['structure'],
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.kuesioner.index')
            ->with('success', 'Struktur kuesioner berhasil diperbarui.');
    }

    public function edit(int $kuesioner): Response
    {
        $row = DB::table('mitra_kuesioner')
            ->select(['id', 'structure', 'status', 'created_at'])
            ->where('id', $kuesioner)
            ->first();

        if (! $row) {
            abort(404);
        }

        $decodedStructure = json_decode($row->structure, true);

        return Inertia::render('AdminKuesionerEditPage', [
            'kuesioner' => [
                'id' => (int) $row->id,
                'status' => $row->status,
                'structure' => is_array($decodedStructure) ? $decodedStructure : null,
                'created_at' => $row->created_at,
            ],
        ]);
    }

    private function validateStructureContent(string $structure): ?string
    {
        /** @var mixed $decodedStructure */
        $decodedStructure = json_decode($structure, true);
        if (! is_array($decodedStructure) || ! array_key_exists('questions', $decodedStructure) || ! is_array($decodedStructure['questions'])) {
            return 'Struktur JSON tidak valid. Field "questions" harus berupa array.';
        }

        foreach ($decodedStructure['questions'] as $index => $question) {
            if (! is_array($question)) {
                return sprintf('Pertanyaan ke-%d tidak valid.', $index + 1);
            }

            if (! array_key_exists('is_scoring', $question) || ! is_bool($question['is_scoring'])) {
                return sprintf('Pertanyaan ke-%d wajib memiliki props "is_scoring" bernilai boolean.', $index + 1);
            }

            $hasRespondentVisibility = array_key_exists('is_showing_respondent', $question) && is_bool($question['is_showing_respondent']);
            $hasAssessorVisibility = array_key_exists('is_showing_assessor', $question) && is_bool($question['is_showing_assessor']);
            $hasLegacyVisibility = array_key_exists('is_showing', $question) && is_bool($question['is_showing']);

            if (! $hasRespondentVisibility && ! $hasLegacyVisibility) {
                return sprintf('Pertanyaan ke-%d wajib memiliki props "is_showing_respondent" bernilai boolean.', $index + 1);
            }

            if (! $hasAssessorVisibility) {
                return sprintf('Pertanyaan ke-%d wajib memiliki props "is_showing_assessor" bernilai boolean.', $index + 1);
            }

            if (! array_key_exists('is_validation', $question) || ! is_bool($question['is_validation'])) {
                return sprintf('Pertanyaan ke-%d wajib memiliki props "is_validation" bernilai boolean.', $index + 1);
            }

            if (array_key_exists('rows', $question)) {
                return sprintf('Pertanyaan ke-%d tidak boleh menggunakan props "rows".', $index + 1);
            }

            if (array_key_exists('maxLength', $question)) {
                return sprintf('Pertanyaan ke-%d tidak boleh menggunakan props "maxLength".', $index + 1);
            }

            if (
                array_key_exists('type', $question)
                && $question['type'] === 'label'
                && $question['is_validation']
            ) {
                return sprintf('Pertanyaan ke-%d bertipe "label" tidak boleh menggunakan validasi.', $index + 1);
            }

            if ($question['is_validation']) {
                if (! array_key_exists('validationRegex', $question) || ! is_string($question['validationRegex']) || trim($question['validationRegex']) === '') {
                    return sprintf('Pertanyaan ke-%d wajib memiliki "validationRegex" saat "is_validation" bernilai true.', $index + 1);
                }

                $normalizedPattern = str_replace('~', '\\~', $question['validationRegex']);
                set_error_handler(static fn () => true);
                $isValidRegex = preg_match('~'.$normalizedPattern.'~', '') !== false;
                restore_error_handler();

                if (! $isValidRegex) {
                    return sprintf('Pertanyaan ke-%d memiliki pola "validationRegex" yang tidak valid.', $index + 1);
                }

                if (array_key_exists('validationMessage', $question) && ! is_string($question['validationMessage'])) {
                    return sprintf('Pertanyaan ke-%d memiliki "validationMessage" yang tidak valid.', $index + 1);
                }
            } elseif (
                (array_key_exists('validationRegex', $question) && is_string($question['validationRegex']) && trim($question['validationRegex']) !== '')
                || (array_key_exists('validationMessage', $question) && is_string($question['validationMessage']) && trim($question['validationMessage']) !== '')
            ) {
                return sprintf('Pertanyaan ke-%d tidak boleh memiliki rule validasi saat "is_validation" bernilai false.', $index + 1);
            }

            if ($question['is_scoring']) {
                if (! array_key_exists('scoringOptions', $question) || ! is_array($question['scoringOptions']) || $question['scoringOptions'] === []) {
                    return sprintf('Pertanyaan ke-%d wajib memiliki "scoringOptions" saat "is_scoring" bernilai true.', $index + 1);
                }

                foreach ($question['scoringOptions'] as $optionIndex => $scoringOption) {
                    if (
                        ! is_array($scoringOption)
                        || ! array_key_exists('label', $scoringOption)
                        || ! is_string($scoringOption['label'])
                        || trim($scoringOption['label']) === ''
                        || ! array_key_exists('score', $scoringOption)
                        || ! is_int($scoringOption['score'])
                    ) {
                        return sprintf(
                            'Pertanyaan ke-%d, opsi scoring ke-%d harus memiliki "label" dan "score" integer.',
                            $index + 1,
                            $optionIndex + 1,
                        );
                    }
                }
            } elseif (array_key_exists('scoringOptions', $question) && is_array($question['scoringOptions']) && $question['scoringOptions'] !== []) {
                return sprintf('Pertanyaan ke-%d tidak boleh memiliki "scoringOptions" saat "is_scoring" bernilai false.', $index + 1);
            }
        }

        return null;
    }
}
