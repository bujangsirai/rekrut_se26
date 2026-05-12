# Admin Kuesioner Notes

This note explains how questionnaire JSON is built and saved from `AdminKuesionerPage.vue`.

## Where It Is Used

- Admin builder UI: `resources/js/pages/AdminKuesionerPage.vue`
- Save + validation: `app/Http/Controllers/AdminKuesionerController.php`
- DB table: `mitra_kuesioner`
- `id`
- `structure` (JSON string)
- `status` (`active` or `inactive`)

## Current Admin UI Flow

- Halaman utama hanya menampilkan:
  - tombol `Tambah Kuesioner`
  - tabel `Riwayat Kuesioner Tersimpan`
- Saat klik `Tambah Kuesioner`, form builder dibuka dalam modal (mode create).
- Field `Judul Form` dan `Deskripsi Form` memakai `TanStackInput`.
- Preview JSON tidak ditampilkan saat proses tambah kuesioner (sesuai penyederhanaan UI).
- Kolom tabel utama: `Judul`, `Deskripsi`, `Jumlah Pertanyaan`, `Status`, dan tombol aksi `Modified Form`.
- Tombol `Modified Form` membuka modal edit untuk kuesioner baris tersebut (title/description + edit pertanyaan).
- Saat create, `status` tidak diinput dari UI dan otomatis disimpan sebagai `inactive`.

## JSON Structure (Top Level)

```json
{
  "title": "Form Wawancara",
  "description": "Silakan isi pertanyaan berikut.",
  "questions": []
}
```

## Question Structure

Minimal required fields per question:

- `name` (string, unique in one form)
- `label` (string)
- `type` (`text` | `textarea` | `select` | `radio`)
- `scoring` (number)

Optional fields:

- `required` (boolean)
- `placeholder` (string)
- `helpText` (string)
- `rows` (number, usually for `textarea`)
- `maxLength` (number)
- `options` (array of `{ label, value }`, required for `radio/select`)
- `scoringOptions` (array of `{ label, score }`)

## Scoring Modes in Admin UI

Each question can use one of two scoring modes:

1. `Skor tunggal`
- Uses one integer value in `scoring`.

2. `Opsi skor`
- Uses multiple labeled score options in `scoringOptions`.
- Format in UI textarea per line: `label|score`
- Example:
  - `Best answer|10`
  - `Good answer|8`
  - `Bad answer|3`

When `scoringOptions` is used, `scoring` is still saved (set to the highest option score) for compatibility.

## Example Question with Scoring Options

```json
{
  "name": "motivasi",
  "label": "Bagaimana motivasi Anda bergabung?",
  "type": "textarea",
  "required": true,
  "scoring": 10,
  "scoringOptions": [
    { "label": "Best answer", "score": 10 },
    { "label": "Good answer", "score": 8 },
    { "label": "Bad answer", "score": 3 }
  ]
}
```

## Backend Validation Rules (Current)

In `AdminKuesionerController@store`:

- `structure` must be valid JSON.
- `status` otomatis `inactive` saat create (tidak dari input user).
- Top-level `questions` must be an array.
- Every question must have numeric `scoring`.
- If `scoringOptions` exists:
  - must be a non-empty array
  - each item must have:
    - `label` non-empty string
    - `score` integer

## Update Behavior

- Route update tersedia: `PUT /admin/kuesioner/{kuesioner}`.
- Update hanya mengubah `structure` (status yang sudah ada dipertahankan).
