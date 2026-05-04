<script setup lang="ts">
import { computed, ref } from 'vue';

defineOptions({
    // @ts-ignore
    layout: null,
});

const nik = ref('');
const isTermsModalOpen = ref(false);
const hasScrolledToBottom = ref(false);

const canContinueRegister = computed(() => hasScrolledToBottom.value);

function openTermsModal(): void {
    isTermsModalOpen.value = true;
    hasScrolledToBottom.value = false;
}

function closeTermsModal(): void {
    isTermsModalOpen.value = false;
}

function onTermsScroll(event: Event): void {
    const target = event.target as HTMLElement;
    const reachedBottom = target.scrollTop + target.clientHeight >= target.scrollHeight - 4;

    if (reachedBottom) {
        hasScrolledToBottom.value = true;
    }
}
</script>

<template>
    <div class="min-h-screen bg-linear-to-b from-cyan-50 via-slate-100 to-slate-200 px-2 py-5 sm:px-6 sm:py-10 lg:py-12">
        <div class="mx-auto flex min-h-[calc(100vh-2.5rem)] w-full max-w-6xl items-center sm:min-h-[calc(100vh-5rem)] lg:min-h-[calc(100vh-6rem)]">
            <div class="w-full space-y-4 sm:space-y-6 lg:space-y-8">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-3 py-2.5 sm:px-6 sm:py-4">
                    <h2 class="text-[15px] leading-snug font-bold tracking-normal text-white sm:text-lg lg:text-xl">
                        REKRUITMEN MITRA BPS KABUPATEN SUMBAWA BARAT
                    </h2>
                </header>

                <div class="space-y-4 p-4 text-[14px] leading-relaxed text-slate-800 sm:space-y-5 sm:p-7 sm:text-base">
                    <p>
                        Pendaftaran calon petugas pengolahan SE2026 telah dibuka. Silakan baca syarat dan ketentuan terlebih dahulu sebelum
                        melanjutkan pendaftaran.
                    </p>

                    <div class="flex justify-start sm:justify-end">
                        <button
                            type="button"
                            @click="openTermsModal"
                            class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-center text-[13px] leading-tight font-semibold text-white transition hover:bg-green-700 sm:h-auto sm:w-auto sm:px-5 sm:py-2.5 sm:text-base"
                        >
                            Daftar Sebagai Petugas Pengolahan SE2026
                        </button>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-3 py-2.5 sm:px-6 sm:py-4">
                    <h2 class="text-[15px] leading-snug font-bold tracking-normal text-white sm:text-lg lg:text-xl">CEK STATUS PENDAFTARAN</h2>
                </header>

                <div class="space-y-4 p-4 text-[14px] text-slate-800 sm:space-y-5 sm:p-7 sm:text-base">
                    <p>Masukkan NIK untuk mengecek apakah Anda sudah terdaftar atau belum :</p>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input
                            v-model="nik"
                            type="text"
                            maxlength="16"
                            placeholder="Masukkan NIK"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm transition outline-none placeholder:text-slate-400 focus:border-cyan-600 sm:h-11 sm:rounded-lg sm:text-base"
                        />
                        <button
                            type="button"
                            class="h-11 cursor-pointer rounded-xl bg-green-600 px-5 text-sm font-semibold text-white transition hover:bg-green-700 sm:h-11 sm:shrink-0 sm:rounded-lg sm:text-base"
                        >
                            Cek Status
                        </button>
                    </div>
                </div>
            </section>
            </div>
        </div>

        <div v-if="isTermsModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 px-3 py-6">
            <div class="w-full max-w-4xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
                <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-base font-bold text-white sm:text-lg">Syarat & Ketentuan Pendaftaran</h3>
                        <button
                            type="button"
                            aria-label="Tutup modal"
                            class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-md border border-white/35 text-xl leading-none font-semibold text-white transition hover:bg-white/15"
                            @click="closeTermsModal"
                        >
                            ×
                        </button>
                    </div>
                </header>

                <div
                    class="max-h-[68vh] space-y-4 overflow-y-auto p-4 text-sm leading-relaxed text-slate-800 sm:p-6 sm:text-base"
                    @scroll="onTermsScroll"
                >
                    <p>
                        Pengolahan Hasil Pendataan ST2023 akan dilaksanakan pada rentang bulan Juli-September 2023. Kualifikasi petugas yang
                        dibutuhkan adalah sebagai berikut:
                    </p>

                    <ul class="list-disc space-y-1.5 pl-4.5 sm:pl-7">
                        <li>Hanya boleh mendaftar pada satu posisi <strong>Receiving Batching, Editing Coding, atau Entri Data</strong>.</li>
                        <li>Diutamakan berpendidikan minimal <strong>tamat SMA/sederajat</strong>.</li>
                        <li>Diutamakan berumur <strong>17-50 Tahun</strong>.</li>
                        <li>Sehat jasmani dan rohani.</li>
                        <li>Berdomisili di Kabupaten Sumbawa Barat.</li>
                        <li>
                            Bersedia bekerja terikat kontrak selama bulan <strong>Juli-Agustus 2023</strong> untuk petugas Receiving Batching dan
                            Editing Coding serta bulan <strong>Juli-September 2023</strong> untuk petugas Entri Data.
                        </li>
                        <li>Disiplin, teliti, dan berkomitmen.</li>
                        <li>Mampu mengoperasikan komputer dengan baik.</li>
                        <li>Mampu bekerjasama dan berkoordinasi dengan baik.</li>
                        <li>
                            Petugas diutamakan <strong>tidak berstatus ASN</strong> (PNS dan P3K) dan tidak memiliki kontrak tertulis dengan
                            Kementrian/Lembaga/Dinas/Instansi apapun baik pemerintah, BUMN/BUMD, maupun swasta atau tidak memiliki pekerjaan tetap
                            agar dapat fokus melaksanakan kegiatan ST2023 sesuai yang tertuang dalam kontrak kerja.
                        </li>
                        <li>Diutamakan berpengalaman dengan Sensus dan Survei BPS (Pengolahan Data) serta berkinerja baik.</li>
                        <li>Diutamakan yang tidak sedang hamil.</li>
                        <li>Mengikuti akun media sosial BPS Kabupaten Sumbawa Barat.</li>
                        <li>
                            Mengisi <strong>Formulir Pendaftaran</strong> pada tautan di bawah dan mendaftar di SOBAT BPS (<a
                                class="text-blue-600 underline hover:text-blue-700"
                                href="https://simitra.bps.go.id"
                                target="_blank"
                                >simitra.bps.go.id</a
                            >).
                        </li>
                    </ul>

                    <p>
                        Jika NIK sudah terdaftar dan ingin memperbaharui data, dapat menghubungi WhatsApp PST BPS Kabupaten Sumbawa Barat di nomor
                        <strong>0821 4440 6055</strong>.
                    </p>
                </div>

                <footer
                    class="flex flex-col gap-2 border-t border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <p class="text-xs text-slate-600 sm:text-sm">Scroll sampai bawah untuk mengaktifkan tombol daftar.</p>
                    <div class="flex gap-2 sm:justify-end">
                        <button
                            type="button"
                            class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                            @click="closeTermsModal"
                        >
                            Tutup
                        </button>
                        <a
                            href="/daftar"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-green-600 px-4 text-sm font-semibold text-white transition hover:bg-green-700 disabled:pointer-events-none disabled:opacity-50"
                            :class="{ 'pointer-events-none opacity-50': !canContinueRegister }"
                        >
                            Lanjut Daftar
                        </a>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</template>
