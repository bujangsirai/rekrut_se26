<script setup lang="ts">
import { TanStackInput } from '@/components/ui/form';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { computed, ref, watch } from 'vue';

defineOptions({
    // @ts-ignore
    layout: null,
});

const isTermsModalOpen = ref(false);
const hasScrolledToBottom = ref(false);

const form = useForm({
    defaultValues: {
        nik: '',
    },
    onSubmit: async ({ value }) => {
        router.post(
            '/cek-status',
            { nik: value.nik },
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Success handling
                },
            },
        );
    },
});

const nikValue = form.useStore((state) => state.values.nik);
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

const page = usePage();
</script>

<template>
    <Head title="Rekrutmen Mitra SE2026" />
    <div class="min-h-screen bg-linear-to-b from-cyan-50 via-slate-100 to-slate-200 px-2 py-5 sm:px-6 sm:py-10 lg:py-12">
        <div class="mx-auto flex min-h-[calc(100vh-2.5rem)] w-full max-w-6xl items-center sm:min-h-[calc(100vh-5rem)] lg:min-h-[calc(100vh-6rem)]">
            <div class="w-full space-y-4 sm:space-y-6 lg:space-y-8">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-3 py-2.5 sm:px-6 sm:py-4">
                        <h2 class="text-[15px] leading-snug font-bold tracking-normal text-white sm:text-lg lg:text-xl">
                            REKRUTMEN MITRA BPS KABUPATEN SUMBAWA BARAT
                        </h2>
                    </header>

                    <div class="space-y-4 p-4 text-[14px] leading-relaxed text-slate-800 sm:space-y-5 sm:p-7 sm:text-base">
                        <p>
                            Pendaftaran calon petugas
                            <b> Sensus Ekonomi 2026 </b>
                            telah dibuka. Silakan baca syarat dan ketentuan terlebih dahulu sebelum melanjutkan pendaftaran.
                        </p>

                        <div class="flex justify-start sm:justify-end">
                            <button
                                type="button"
                                @click="openTermsModal"
                                class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-center text-[13px] leading-tight font-semibold text-white transition hover:bg-green-700 sm:h-auto sm:w-auto sm:px-5 sm:py-2.5 sm:text-base"
                            >
                                Daftar Sebagai Petugas SE2026
                            </button>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-3 py-2.5 sm:px-6 sm:py-4">
                        <h2 class="text-[15px] leading-snug font-bold tracking-normal text-white sm:text-lg lg:text-xl">CEK STATUS PENDAFTARAN</h2>
                    </header>

                    <div class="space-y-4 p-4 text-[14px] text-slate-800 sm:space-y-5 sm:p-7 sm:text-base">
                        <div class="space-y-3">
                            <label class="block text-[14px] font-medium text-slate-800 sm:text-base">
                                Masukkan NIK untuk mengecek apakah Anda sudah terdaftar atau belum :
                            </label>
                            <form
                                class="flex flex-col gap-3 sm:flex-row sm:items-start"
                                @submit.prevent="
                                    (e) => {
                                        e.stopPropagation();
                                        form.handleSubmit();
                                    }
                                "
                            >
                                <div class="flex-1">
                                    <TanStackInput
                                        :form="form"
                                        name="nik"
                                        label=""
                                        placeholder="Contoh: 5207xxxxxxxxxxxx"
                                        :number-only="true"
                                        :maxlength="16"
                                        :validators="{
                                            // @ts-ignore
                                            onSubmit: ({ value }) => {
                                                if (!value) return 'NIK wajib diisi';
                                                if (value.length !== 16) return 'NIK harus 16 angka';
                                                return undefined;
                                            },
                                        }"
                                    />
                                </div>
                                <button
                                    type="submit"
                                    class="h-11 cursor-pointer rounded-xl bg-green-600 px-5 text-sm font-semibold text-white transition hover:bg-green-700 sm:h-11 sm:shrink-0 sm:rounded-lg sm:text-base"
                                >
                                    Cek Status
                                </button>
                            </form>
                        </div>
                    </div>
                </section>

                <div class="text-center">
                    <p class="text-[13px] text-slate-600 sm:text-[15px]">
                        Ada kendala atau pertanyaan? Silakan hubungi WhatsApp PST BPS KSB:
                        <a
                            href="https://wa.me/6282144406055"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-semibold text-cyan-700 hover:text-cyan-800 hover:underline"
                        >
                            0821 4440 6055
                        </a>
                    </p>
                </div>
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
                        Pendaftaran calon petugas <strong>Sensus Ekonomi 2026</strong> telah dibuka. Kualifikasi petugas yang dibutuhkan adalah
                        sebagai berikut:
                    </p>
                    <ul class="list-disc space-y-1.5 pl-4.5 sm:pl-7">
                        <li>Bukan Aparatur Sipil Negara (ASN);</li>
                        <li>Sehat jasmani dan rohani;</li>
                        <li>Disiplin dan berkomitmen;</li>
                        <li>Bersedia bekerja terikat kontrak;</li>
                        <li>Mampu berbahasa Indonesia dengan baik serta membaca dan menulis huruf latin;</li>
                        <li>Mampu berkomunikasi dengan baik;</li>
                        <li>Pendidikan minimal tamat SMA, <strong>diutamakan Mahasiswa/Sarjana</strong>;</li>
                        <li>Berdomisili di wilayah pendataan;</li>
                        <li>Bersedia mengikuti pelatihan dan lulus diatas <i>passing grade;</i></li>
                        <li>Diutamakan berumur 18 - 50 tahun pada saat registrasi;</li>
                        <li>Memiliki/menguasai dan dapat menggunakan tablet/<i>smartphone;</i></li>
                        <li>Memiliki/menguasai dan mampu mengendarai kendaraan bermotor;</li>
                        <li>
                            Mampu bekerjasama dan berkoordinasi dengan anggota tim, pegawai BPS, Aparatur Desa/Kelurahan, Ketua/Pengurus SLS, dan
                            lain-lain;
                        </li>
                        <li>
                            Melakukan registrasi secara mandiri pada Aplikasi (melalui website
                            <a href="https://mitra.bps.go.id" target="_blank" class="font-semibold text-cyan-600 underline hover:text-cyan-700"
                                >Mitra BPS</a
                            >);
                        </li>
                        <li>
                            Memiliki/Menguasai tablet/smartphone dengan spesifikasi minimum:
                            <ul class="mt-1.5 list-[circle] space-y-1 pl-5">
                                <li>OS Android minimum 7 (Nougat)</li>
                                <li>Layar minimum 5 inch</li>
                                <li>Processor Quadcore minimum 1,4GHz</li>
                                <li>RAM Minimum 4 GB (Free 2 GB)</li>
                                <li>Storage Internal 32 GB (Free 4 GB)</li>
                                <li>Terkoneksi dengan internet (wifi atau 3G/4G/LTE)</li>
                                <li>GPS harus aktif</li>
                            </ul>
                        </li>
                    </ul>
                    <p>
                        Jika ada pertanyaan terkait mekanisme pendaftaran, silakan hubungi WhatsApp PST BPS Kabupaten Sumbawa Barat di nomor
                        <strong>0821 4440 6055</strong> atau
                        <a
                            href="https://wa.me/6282144406055?text=Halo!%20saya%20ingin%20bertanya%20terkait%20pendaftaran%20SE%202026"
                            target="_blank"
                            class="font-semibold text-cyan-600 underline hover:text-cyan-700"
                            >klik disini</a
                        >.
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
                        <Link
                            href="/daftar"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-green-600 px-4 text-sm font-semibold text-white transition hover:bg-green-700 disabled:pointer-events-none disabled:opacity-50"
                            :class="{ 'pointer-events-none opacity-50': !canContinueRegister }"
                        >
                            Lanjut Daftar
                        </Link>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</template>
