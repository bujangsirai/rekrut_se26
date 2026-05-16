# Admin Kuesioner Notes

This note explains the current questionnaire JSON contract used by:

- `resources/js/pages/AdminKuesionerPage.vue`
- `resources/js/pages/AdminKuesionerEditPage.vue`
- `resources/js/pages/WawancaraMulaiPage.vue`
- `resources/js/pages/WawancaraNilaiPage.vue`
- `app/Http/Controllers/AdminKuesionerController.php`

## View Mapping

- `resources/js/pages/WawancaraMulaiPage.vue` = **Respondent View**
- `resources/js/pages/WawancaraNilaiPage.vue` = **Assessor View**

Current rendering behavior:

- Respondent view only renders questions with `is_showing_respondent = true`.
- Assessor view only renders questions with `is_showing_assessor = true`.
- Legacy compatibility: if old `is_showing` exists, it is treated as respondent visibility.

## Top-Level JSON

```json
{
  "title": "Form Wawancara",
  "description": "Silakan isi pertanyaan berikut.",
  "questions": []
}
```

## Question Structure (Refactored)

Required fields per question:

- `name` (string, unique in one form)
- `label` (string)
- `type` (`text` | `textarea` | `select` | `radio` | `checkbox` | `label`)
- `is_showing_respondent` (boolean)
- `is_showing_assessor` (boolean)
- `is_scoring` (boolean)
- `is_validation` (boolean)

Optional fields:

- `required` (boolean)
- `placeholder` (string)
- `helpText` (string)
- `options` (array of `{ label, value }`, required for `radio/select/checkbox`)
- `scoringOptions` (array of `{ label, score }`, required when `is_scoring = true`)
- `validationRegex` (string RegExp pattern)
- `validationMessage` (string)

Deprecated / not allowed:

- `rows` is no longer used in structure.
- `scoring` (single numeric score) is no longer used for new structure.
- `maxLength` is no longer used in structure.

## Label Type Behavior

- `type: "label"` is read-only (no input).
- Label HTML is rendered using `v-html`.
- Renderer supports content in `label` and compatibility fallback from `value`.
- Label dapat menggunakan scoring (`is_scoring = true`) jika dibutuhkan di sisi assessor.

Example:

```json
{
  "name": "judul_1",
  "type": "label",
  "label": "<h2>INI H2</h2>",
  "is_showing_respondent": true,
  "is_showing_assessor": true,
  "is_scoring": true,
  "scoringOptions": [
    { "label": "Poin penuh", "score": 10 },
    { "label": "Poin sedang", "score": 6 },
    { "label": "Poin rendah", "score": 3 }
  ],
  "is_validation": false
}
```

## Visibility Rule (`is_showing_respondent` & `is_showing_assessor`)

- `is_showing_respondent = true`: tampil di responden.
- `is_showing_respondent = false`: disembunyikan dari responden.
- `is_showing_assessor = true`: tampil di assessor.
- `is_showing_assessor = false`: disembunyikan dari assessor.

## Scoring Rule (Refactored)

- Only one scoring style is used: `scoringOptions` with integer scores.
- To enable scoring for a question: set `is_scoring` to `true`.
- If `is_scoring` is `false`, `scoringOptions` should be omitted / empty.

Example:

```json
{
  "name": "aktivitas_harian",
  "label": "Apa saja kegiatan harian Anda?",
  "type": "textarea",
  "required": true,
  "is_showing_respondent": true,
  "is_showing_assessor": true,
  "is_scoring": true,
  "scoringOptions": [
    { "label": "Tidak ada Aktivitas", "score": 10 },
    { "label": "Aktivitas setengah hari", "score": 6 },
    { "label": "Aktivitas sampai sore", "score": 3 }
  ]
}
```

## Checkbox Type (Multiple Choice)

- `type: "checkbox"` mendukung multi-pilihan (bisa pilih lebih dari satu).
- Nilai jawaban disimpan sebagai array string pada `value`.

Example:

```json
{
  "name": "ketersediaan_hari",
  "label": "Hari yang tersedia untuk bertugas",
  "type": "checkbox",
  "required": true,
  "is_showing_respondent": true,
  "is_showing_assessor": true,
  "is_scoring": false,
  "is_validation": false,
  "options": [
    { "label": "Senin", "value": "senin" },
    { "label": "Selasa", "value": "selasa" },
    { "label": "Rabu", "value": "rabu" }
  ]
}
```

## RegExp Validation Rule

For input-based question types (`text` / `textarea`):

- Enable validation by setting `is_validation` to `true`.
- If `is_validation = true`, `validationRegex` must be provided.
- Validation runs on `keyup`.
- If value does not match, error is shown from `validationMessage` (or default message).

Example:

```json
{
  "name": "nik",
  "label": "Masukkan NIK",
  "type": "text",
  "required": true,
  "is_showing_respondent": true,
  "is_showing_assessor": true,
  "is_scoring": false,
  "is_validation": true,
  "validationRegex": "^[0-9]{16}$",
  "validationMessage": "NIK harus 16 digit angka."
}
```

## Backend Validation Summary

In `AdminKuesionerController`:

- `structure` must be valid JSON.
- Top-level `questions` must be an array.
- Each question must contain boolean `is_scoring`.
- Each question must contain boolean `is_showing_respondent`.
- Each question must contain boolean `is_showing_assessor`.
- Each question must contain boolean `is_validation`.
- `rows` is rejected.
- `maxLength` is rejected.
- If `is_scoring = true`, `scoringOptions` must exist and each option must have:
  - `label` non-empty string
  - `score` integer
- If `is_scoring = false`, non-empty `scoringOptions` is rejected.
- If `is_validation = true`, `validationRegex` is required and validated.
- If `is_validation = false`, validation fields must be empty.
