<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicRegistrationOptionalRequest;
use App\Http\Requests\PublicRegistrationRequest;
use App\Models\ApplicantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PublicRegistrationController extends Controller
{
    public function create(): Response
    {
        [$kecamatanOptions, $desaOptions] = $this->registrationLocationOptions();

        return Inertia::render('PublicRegistrationPage', [
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
        ]);
    }

    public function createOptional(): Response
    {
        [$kecamatanOptions, $desaOptions] = $this->registrationLocationOptions();

        return Inertia::render('PublicRegistrationOptionalUploadPage', [
            'kecamatanOptions' => $kecamatanOptions,
            'desaOptions' => $desaOptions,
        ]);
    }

    /**
     * @return array{0: Collection<int, array{kode_kec: string, nama_kec: string}>, 1: Collection<int, array{kode_kec: string, kode_desa: string, nama_desa: string}>}
     */
    private function registrationLocationOptions(): array
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

        return [$kecamatanOptions, $desaOptions];
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

    public function storeOptional(PublicRegistrationOptionalRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $ktpPath = $request->file('ktp_file')?->store('mitra/ktp', 'local');
        $spekHpPath = $request->file('spek_hp_file')?->store('mitra/spek_hp', 'local');
        $followIgPath = $request->file('follow_ig_file')?->store('mitra/follow_ig', 'local');

        unset($validated['ktp_file'], $validated['spek_hp_file'], $validated['follow_ig_file']);

        DB::transaction(function () use ($validated, $ktpPath, $spekHpPath, $followIgPath): void {
            ApplicantProfile::query()->create([
                ...$validated,
                'kode_akses' => Str::uuid()->toString(),
                'kode_akses_kedaluwarsa_pada' => now()->addMonths(3),
                'status_wawancara' => 'Belum Wawancara',
                'status_kelulusan' => 'Belum Lulus',
            ]);

            $berkasPayload = [];

            if ($ktpPath) {
                $berkasPayload[] = [
                    'nik' => $validated['nik'],
                    'jenis_berkas' => 'ktp',
                    'file_path' => $ktpPath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($spekHpPath) {
                $berkasPayload[] = [
                    'nik' => $validated['nik'],
                    'jenis_berkas' => 'spek_hp',
                    'file_path' => $spekHpPath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($followIgPath) {
                $berkasPayload[] = [
                    'nik' => $validated['nik'],
                    'jenis_berkas' => 'follow_ig',
                    'file_path' => $followIgPath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($berkasPayload !== []) {
                DB::table('mitra_berkas')->insert($berkasPayload);
            }
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

    public function selection(Request $request, string $kode_akses): Response|RedirectResponse
    {
        $mitra = $this->findMitraByAccessCode($kode_akses);

        if ($mitra->kode_akses_kedaluwarsa_pada && $mitra->kode_akses_kedaluwarsa_pada->isPast()) {
            abort(403, 'Kode akses telah kedaluwarsa.');
        }

        if ($mitra->status_wawancara === 'Sudah Wawancara') {
            $request->session()->put('status_nik', $mitra->nik);

            return redirect()->route('public.status');
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

        $formConfig = $this->activeFormConfig();

        return Inertia::render('WawancaraMulaiPage', [
            'mitra' => $mitra,
            'formConfig' => $formConfig,
        ]);
    }

    public function assessorSelection(string $kode_akses): Response
    {
        $mitra = $this->findMitraByAccessCode($kode_akses);
        $formConfig = $this->activeFormConfig();

        return Inertia::render('WawancaraNilaiPage', [
            'mitra' => $mitra,
            'formConfig' => $formConfig,
        ]);
    }

    public function submitAssessorSelection(Request $request, string $kode_akses): RedirectResponse
    {
        $mitra = $this->findMitraByAccessCode($kode_akses);
        $formConfig = $this->activeFormConfig();

        $scoringQuestions = collect($formConfig['questions'] ?? [])
            ->filter(function (mixed $question): bool {
                if (! is_array($question)) {
                    return false;
                }

                return $this->isVisibleForAssessor($question)
                    && ($question['is_scoring'] ?? false) === true
                    && is_array($question['scoringOptions'] ?? null)
                    && ($question['scoringOptions'] ?? []) !== [];
            })
            ->values();

        $rules = [
            'scores' => ['required', 'array'],
            'notes' => ['nullable', 'array'],
        ];

        foreach ($scoringQuestions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $name = (string) ($question['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $allowedScores = collect((array) ($question['scoringOptions'] ?? []))
                ->filter(static fn (mixed $option): bool => is_array($option) && array_key_exists('score', $option))
                ->map(static fn (array $option): int => (int) $option['score'])
                ->values()
                ->all();

            $fieldRules = ['required', 'integer'];
            if ($allowedScores !== []) {
                $fieldRules[] = Rule::in($allowedScores);
            }

            $rules["scores.$name"] = $fieldRules;
            $rules["notes.$name"] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);
        $submittedScores = (array) ($validated['scores'] ?? []);
        $submittedNotes = (array) ($validated['notes'] ?? []);

        $penilaianKuesionerItems = [];
        $totalScore = 0;

        foreach ($scoringQuestions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $name = (string) ($question['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $score = (int) ($submittedScores[$name] ?? 0);
            $note = trim((string) ($submittedNotes[$name] ?? ''));
            $totalScore += $score;

            $penilaianKuesionerItems[$name] = [
                'label' => (string) ($question['label'] ?? ''),
                'type' => (string) ($question['type'] ?? 'text'),
                'score' => $score,
                'note' => $note,
            ];
        }

        $assessorUser = $request->user();
        $assessorName = '';
        if ($assessorUser) {
            $assessorName = (string) ($assessorUser->name ?? $assessorUser->email ?? $assessorUser->id ?? '');
        }

        $penilaianKuesioner = [
            'assessor' => $assessorName,
            'time' => now()->setTimezone('Asia/Makassar')->format('Y-m-d H:i:s P'),
            ...$penilaianKuesionerItems,
        ];

        $mitra->update([
            'penilaian_kuesioner' => $penilaianKuesioner,
            'skor_kuesioner' => $totalScore,
            'terakhir_diakses_pada' => now(),
        ]);

        return back()->with('success', 'Penilaian berhasil disimpan.');
    }

    public function submitSelection(Request $request, string $kode_akses): RedirectResponse
    {
        $mitra = $this->findMitraByAccessCode($kode_akses);

        if ($mitra->kode_akses_kedaluwarsa_pada && $mitra->kode_akses_kedaluwarsa_pada->isPast()) {
            abort(403, 'Kode akses telah kedaluwarsa.');
        }

        $formConfig = $this->activeFormConfig();

        $respondentQuestions = collect($formConfig['questions'] ?? [])
            ->filter(function (mixed $question): bool {
                if (! is_array($question)) {
                    return false;
                }

                $isLabel = ($question['type'] ?? 'text') === 'label';

                return $this->isVisibleForRespondent($question) && ! $isLabel;
            })
            ->values();

        $normalizedAnswers = (array) $request->input('answers', []);
        foreach ($respondentQuestions as $question) {
            if (! is_array($question) || (($question['type'] ?? 'text') !== 'checkbox')) {
                continue;
            }

            $name = (string) ($question['name'] ?? '');
            if ($name === '') {
                continue;
            }

            if (! array_key_exists($name, $normalizedAnswers)) {
                $normalizedAnswers[$name] = [];

                continue;
            }

            $rawValue = $normalizedAnswers[$name];
            if (is_string($rawValue)) {
                $normalizedAnswers[$name] = [trim($rawValue)];

                continue;
            }

            if (! is_array($rawValue)) {
                $normalizedAnswers[$name] = [];
            }
        }

        $request->merge([
            'answers' => $normalizedAnswers,
        ]);

        $rules = [
            'answers' => ['required', 'array'],
        ];

        foreach ($respondentQuestions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $name = (string) ($question['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $type = (string) ($question['type'] ?? 'text');
            if ($type === 'checkbox') {
                $fieldRules = ['nullable', 'array'];
                if (($question['required'] ?? false) === true) {
                    $fieldRules = ['required', 'array', 'min:1'];
                }

                $rules["answers.$name"] = $fieldRules;

                if (is_array($question['options'] ?? null)) {
                    $optionValues = collect($question['options'])
                        ->filter(static fn (mixed $option): bool => is_array($option) && array_key_exists('value', $option))
                        ->map(static fn (array $option): string => (string) $option['value'])
                        ->values()
                        ->all();

                    if ($optionValues !== []) {
                        $rules["answers.$name.*"] = ['string', Rule::in($optionValues)];
                    }
                }

                continue;
            }

            $fieldRules = ['nullable', 'string'];
            if (($question['required'] ?? false) === true) {
                $fieldRules = ['required', 'string'];
            }

            if (($type === 'radio' || $type === 'select') && is_array($question['options'] ?? null)) {
                $optionValues = collect($question['options'])
                    ->filter(static fn (mixed $option): bool => is_array($option) && array_key_exists('value', $option))
                    ->map(static fn (array $option): string => (string) $option['value'])
                    ->values()
                    ->all();

                if ($optionValues !== []) {
                    $fieldRules[] = Rule::in($optionValues);
                }
            }

            $rules["answers.$name"] = $fieldRules;
        }

        $validated = $request->validate($rules);
        $answers = (array) ($validated['answers'] ?? []);

        foreach ($respondentQuestions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $name = (string) ($question['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $questionType = (string) ($question['type'] ?? 'text');
            $isValidation = ($question['is_validation'] ?? false) === true;
            $pattern = is_string($question['validationRegex'] ?? null) ? trim($question['validationRegex']) : '';

            if ($questionType !== 'checkbox' && $isValidation && $pattern !== '') {
                $currentAnswerRaw = $answers[$name] ?? '';
                $currentAnswer = trim((string) $currentAnswerRaw);

                if ($currentAnswer !== '') {
                    $normalizedPattern = str_replace('~', '\\~', $pattern);
                    set_error_handler(static fn () => true);
                    $matchResult = preg_match('~'.$normalizedPattern.'~', $currentAnswer);
                    restore_error_handler();

                    if ($matchResult !== 1) {
                        $message = is_string($question['validationMessage'] ?? null) && trim($question['validationMessage']) !== ''
                            ? trim((string) $question['validationMessage'])
                            : 'Format jawaban tidak sesuai.';

                        return back()
                            ->withErrors(["answers.$name" => $message])
                            ->withInput();
                    }
                }
            }
        }

        $jawabanKuesioner = [];
        foreach ($respondentQuestions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $name = (string) ($question['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $questionType = (string) ($question['type'] ?? 'text');
            $currentAnswerRaw = $answers[$name] ?? '';
            $storedValue = $questionType === 'checkbox'
                ? (is_array($currentAnswerRaw)
                    ? collect($currentAnswerRaw)->filter(static fn (mixed $value): bool => is_string($value))->map(static fn (string $value): string => trim($value))->filter(static fn (string $value): bool => $value !== '')->values()->all()
                    : [])
                : trim((string) $currentAnswerRaw);

            $jawabanKuesioner[$name] = [
                'label' => (string) ($question['label'] ?? ''),
                'type' => $questionType,
                'value' => $storedValue,
            ];
        }

        $mitra->update([
            'jawaban_kuesioner' => $jawabanKuesioner,
            'status_wawancara' => 'Sudah Wawancara',
            'terakhir_diakses_pada' => now(),
        ]);

        $request->session()->put('status_nik', $mitra->nik);

        return redirect()
            ->route('public.status')
            ->with('success', 'Jawaban wawancara berhasil dikirim.');
    }

    public function saveSelectionDraft(Request $request, string $kode_akses): RedirectResponse
    {
        $mitra = $this->findMitraByAccessCode($kode_akses);

        if ($mitra->kode_akses_kedaluwarsa_pada && $mitra->kode_akses_kedaluwarsa_pada->isPast()) {
            abort(403, 'Kode akses telah kedaluwarsa.');
        }

        $formConfig = $this->activeFormConfig();
        $respondentQuestions = collect($formConfig['questions'] ?? [])
            ->filter(function (mixed $question): bool {
                if (! is_array($question)) {
                    return false;
                }

                $isLabel = ($question['type'] ?? 'text') === 'label';

                return $this->isVisibleForRespondent($question) && ! $isLabel;
            })
            ->values();

        $submittedAnswers = (array) $request->input('answers', []);
        $jawabanKuesioner = [];

        foreach ($respondentQuestions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $name = (string) ($question['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $questionType = (string) ($question['type'] ?? 'text');

            $currentAnswerRaw = $submittedAnswers[$name] ?? '';
            $storedValue = $questionType === 'checkbox'
                ? (is_array($currentAnswerRaw)
                    ? collect($currentAnswerRaw)->filter(static fn (mixed $value): bool => is_string($value))->map(static fn (string $value): string => trim($value))->filter(static fn (string $value): bool => $value !== '')->values()->all()
                    : [])
                : trim((string) $currentAnswerRaw);

            $jawabanKuesioner[$name] = [
                'label' => (string) ($question['label'] ?? ''),
                'type' => $questionType,
                'value' => $storedValue,
            ];
        }

        $mitra->update([
            'jawaban_kuesioner' => $jawabanKuesioner,
            'terakhir_diakses_pada' => now(),
        ]);

        return back()->with('success', 'Jawaban sementara berhasil disimpan.');
    }

    private function findMitraByAccessCode(string $kodeAkses): ApplicantProfile
    {
        $mitra = ApplicantProfile::query()
            ->where('kode_akses', $kodeAkses)
            ->first();

        if (! $mitra) {
            abort(404, 'Kode akses tidak valid.');
        }

        return $mitra;
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function isVisibleForRespondent(array $question): bool
    {
        if (array_key_exists('is_showing_respondent', $question) && is_bool($question['is_showing_respondent'])) {
            return $question['is_showing_respondent'];
        }

        if (array_key_exists('is_showing', $question) && is_bool($question['is_showing'])) {
            return $question['is_showing'];
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function isVisibleForAssessor(array $question): bool
    {
        if (array_key_exists('is_showing_assessor', $question) && is_bool($question['is_showing_assessor'])) {
            return $question['is_showing_assessor'];
        }

        return true;
    }

    /**
     * @return array{title: string, description: string, questions: array<int, array<string, mixed>>}
     */
    private function activeFormConfig(): array
    {
        $activeKuesioner = DB::table('mitra_kuesioner')
            ->select(['structure'])
            ->where('status', 'active')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        $decodedStructure = $activeKuesioner ? json_decode($activeKuesioner->structure, true) : null;
        $formConfig = is_array($decodedStructure)
            ? $decodedStructure
            : [
                'title' => 'Form Wawancara',
                'description' => '',
                'questions' => [],
            ];

        if (! array_key_exists('questions', $formConfig) || ! is_array($formConfig['questions'])) {
            $formConfig['questions'] = [];
        }

        return $formConfig;
    }
}
