# Saku NG
           **/Modules/Debugging/**
           
Project modular Laravel 12 + Inertia v2 + Vue 3 + Vite untuk aplikasi internal dengan pola navigasi berbasis module.

## Stack
- Laravel 12
- Inertia.js v2 (`@inertiajs/vue3`)
- Vue 3
- Tailwind CSS v4 + shadcn-vue components
- `nwidart/laravel-modules`

## Requirements
- PHP 8.3+ (project ini disiapkan untuk PHP 8.3)
- Composer 2
- Node.js 22
- pnpm 10.x
 
## Menjalankan Project
1. Install dependency PHP dan JS.
2. Jalankan server Laravel + Vite dev server.

```bash
composer install
pnpm install
php artisan key:generate
php artisan serve
pnpm run dev 
```

Catatan:
- Jika perubahan frontend tidak muncul, pastikan `pnpm run dev` aktif.
- Jika memakai Laravel Herd, akses project via domain `.test` yang sesuai.

## Runtime Persistence
- Session aplikasi disimpan di database (`sessions` table).
- Cache aplikasi default disimpan di database (`cache` dan `cache_locks` tables).
- Queue default saat ini memakai koneksi database.
- Setelah menarik perubahan baru yang menambah migration persistence, jalankan:

```bash
php artisan migrate
php artisan config:clear
php artisan cache:clear
```

## Integrasi Google Drive
- Integrasi Google Drive memakai satu akun Google admin yang dihubungkan sekali via OAuth.
- Pegawai tetap login menggunakan auth aplikasi; mereka tidak perlu login Google di device masing-masing.
- `GOOGLE_REFRESH_TOKEN` disimpan di backend sebagai secret jangka panjang.
- `access_token` Google Drive tidak disimpan di browser dan tidak disimpan permanen di `.env`; token ini di-cache server-side lewat Laravel cache.
- Halaman operasional Google Drive tersedia di `/google-drive`.

### Lifecycle Access Token
- Yang direfresh oleh aplikasi adalah `access_token`, bukan `refresh_token`.
- Saat ada request ke Google Drive, backend akan lebih dulu mengecek `access_token` di Laravel cache.
- Jika token cache masih ada dan belum expired, token yang sama dipakai ulang untuk request tersebut.
- Jika token cache kosong atau sudah expired, backend memakai `GOOGLE_REFRESH_TOKEN` untuk meminta `access_token` baru ke Google.
- Token baru lalu disimpan lagi ke cache server-side.
- TTL cache token saat ini diset ke `expires_in - 60 detik`, dengan minimum 60 detik.
- Refresh token flow memakai cache lock, jadi saat banyak request datang bersamaan, hanya satu request yang refresh token dan request lain memakai hasil cache yang sama.

### Environment yang diperlukan
```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
GOOGLE_DRIVE_FOLDER_ID=
GOOGLE_REFRESH_TOKEN= 
```

### Route Google Drive
- `/auth/google/drive` -> menghubungkan akun Google admin dan mengambil `refresh token`
- `/google-drive` -> halaman UI untuk list, upload, rename, delete, dan copy link file

### Catatan Keamanan
- Jangan commit `GOOGLE_CLIENT_SECRET` atau `GOOGLE_REFRESH_TOKEN`.
- Jika secret atau refresh token pernah terekspos, rotate credential Google OAuth dan lakukan consent ulang untuk mendapatkan token baru.
- Seluruh operasi file Google Drive berjalan atas nama akun Google admin yang terhubung, sehingga authorization internal tetap harus dijaga dari sisi Laravel role/auth.

## Arsitektur Module (Ringkas)
Project memakai folder `Modules/`:
- `Core` -> hub utama (`/home`) untuk daftar module internal
- `Tool` -> hub module Tools (`/tools`) + feature pages (contoh: geotagging)
- `Know` -> module halaman knowledge (contoh page)
- `Shared` -> shared UI/layout reusable antar module
- `Umum` -> halaman publik/landing (dikecualikan dari internal persistent layout)

## Pola Routing yang Dipakai
Pola yang dipakai untuk UX modular:
- `/{module}` = halaman hub module
- `/{module}/{feature}` = halaman fitur di dalam module

Contoh:
- `/home` -> hub global module
- `/tools` -> hub module Tools
- `/tools/geotagging-gambar` -> fitur geotagging gambar

## Layout & Navigasi Internal
- Layout internal utama menggunakan `SharedModuleLayout`
- Persistent layout dipasang otomatis dari `resources/js/app.ts` untuk semua module page **kecuali `Umum`**
- Navigasi internal menggunakan `Link` dari Inertia (bukan `<a>`) agar transisi lebih seamless

## Panduan Warna Frontend
- Hindari hard-coded color literal di komponen seperti `#xxxxxx`, `rgb(...)`, `rgba(...)`, `bg-white`, `text-slate-700`, atau gradient dengan warna mentah jika masih bisa dipetakan ke token theme.
- Utamakan token semantik Tailwind yang sudah tersedia di project: `background`, `foreground`, `card`, `popover`, `primary`, `secondary`, `accent`, `muted`, `border`, `input`, `ring`, `destructive`, dan `success`.
- Untuk opacity, tetap gunakan token semantik: contoh `bg-background/80`, `text-primary/90`, `border-border/60`.
- Untuk gradient, shadow, canvas, atau integrasi pihak ketiga yang tidak bisa memakai utility biasa, ambil warna dari CSS theme variables di `resources/css/app.css` daripada menulis hex atau RGB langsung.
- Saat menambah komponen baru di `Modules/`, cek sibling component lebih dulu dan pertahankan pemetaan warna yang konsisten dengan token semantik yang sama.

## File Penting
- `resources/js/app.ts` -> resolver Inertia + auto persistent layout module
- `Modules/Shared/resources/js/components/layouts/SharedModuleLayout.vue` -> shell layout internal
- `Modules/Shared/resources/js/components/modules/ModuleContentShell.vue` -> wrapper konten module (breadcrumbs + shell body)

## Dokumentasi Module
Lihat `README.md` di masing-masing folder module:
- `Modules/Core/README.md`
- `Modules/Know/README.md`
- `Modules/Shared/README.md`
- `Modules/Tool/README.md`
- `Modules/Umum/README.md`
