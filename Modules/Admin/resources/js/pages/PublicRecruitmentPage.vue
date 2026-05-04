<script setup lang="ts">
import { ref } from 'vue';

defineOptions({
    layout: null,
});

const nik = ref('');
</script>

<template>
    <div class="min-h-screen bg-linear-to-b from-cyan-50 via-slate-100 to-slate-200 px-3 py-4 sm:px-6 sm:py-6 lg:py-8">
        <div class="mx-auto max-w-6xl space-y-5">
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-4 py-3 sm:px-6 sm:py-4">
                    <h1 class="text-base font-bold tracking-wide text-white sm:text-lg lg:text-xl">PENGOLAHAN SENSUS PERTANIAN 2023 (ST2023)</h1>
                </header>

                <div class="space-y-5 p-4 text-sm leading-relaxed text-slate-800 sm:p-6 sm:text-base">
                    <p>
                        Pengolahan Hasil Pendataan ST2023 akan dilaksanakan pada rentang bulan Juli-September 2023. Kualifikasi petugas
                        yang dibutuhkan adalah sebagai berikut:
                    </p>

                    <ul class="list-disc space-y-1.5 pl-5 sm:pl-7">
                        <li>Hanya boleh mendaftar pada satu posisi <strong>Receiving Batching, Editing Coding, atau Entri Data</strong>.</li>
                        <li>Diutamakan berpendidikan minimal <strong>tamat SMA/sederajat</strong>.</li>
                        <li>Diutamakan berumur <strong>17-50 Tahun</strong>.</li>
                        <li>Sehat jasmani dan rohani.</li>
                        <li>Berdomisili di Kabupaten Sumbawa Barat.</li>
                        <li>
                            Bersedia bekerja terikat kontrak selama bulan <strong>Juli-Agustus 2023</strong> untuk petugas Receiving Batching
                            dan Editing Coding serta bulan <strong>Juli-September 2023</strong> untuk petugas Entri Data.
                        </li>
                        <li>Disiplin, teliti, dan berkomitmen.</li>
                        <li>Mampu mengoperasikan komputer dengan baik.</li>
                        <li>Mampu bekerjasama dan berkoordinasi dengan baik.</li>
                        <li>
                            Petugas diutamakan <strong>tidak berstatus ASN</strong> (PNS dan P3K) dan tidak memiliki kontrak tertulis dengan
                            Kementrian/Lembaga/Dinas/Instansi apapun baik pemerintah, BUMN/BUMD, maupun swasta atau tidak memiliki pekerjaan
                            tetap agar dapat fokus melaksanakan kegiatan ST2023 sesuai yang tertuang dalam kontrak kerja.
                        </li>
                        <li>Diutamakan berpengalaman dengan Sensus dan Survei BPS (Pengolahan Data) serta berkinerja baik.</li>
                        <li>Diutamakan yang tidak sedang hamil.</li>
                        <li>Mengikuti akun media sosial BPS Kabupaten Sumbawa Barat.</li>
                        <li>
                            Mengisi <strong>Formulir Pendaftaran</strong> pada tautan di bawah dan mendaftar di SOBAT BPS
                            (<a class="text-blue-600 underline hover:text-blue-700" href="https://simitra.bps.go.id" target="_blank">simitra.bps.go.id</a>).
                        </li>
                    </ul>

                    <p>
                        Jika NIK sudah terdaftar dan ingin memperbaharui data, dapat menghubungi WhatsApp PST BPS Kabupaten Sumbawa Barat di
                        nomor <strong>0821 4440 6055</strong>.
                    </p>

                    <div class="flex justify-start sm:justify-end">
                        <a
                            href="#"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-green-600 px-5 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-green-700 sm:w-auto sm:text-base"
                        >
                            Daftar Sebagai Petugas Pengolahan ST2023
                        </a>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-4 py-3 sm:px-6 sm:py-4">
                    <h2 class="text-base font-bold tracking-wide text-white sm:text-lg lg:text-xl">CEK STATUS PENDAFTARAN</h2>
                </header>

                <div class="space-y-4 p-4 text-sm text-slate-800 sm:p-6 sm:text-base">
                    <p>Masukkan NIK untuk mengecek apakah Anda sudah terdaftar atau belum :</p>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input
                            v-model="nik"
                            type="text"
                            maxlength="16"
                            placeholder="Masukkan NIK"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-cyan-600 sm:text-base"
                        />
                        <button
                            type="button"
                            class="h-11 rounded-lg bg-green-600 px-5 text-sm font-semibold text-white transition hover:bg-green-700 sm:shrink-0 sm:text-base"
                        >
                            Cek Status
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
