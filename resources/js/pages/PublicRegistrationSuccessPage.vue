<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, MessageCircle } from 'lucide-vue-next';

defineOptions({
    // @ts-ignore
    layout: null,
});

defineProps<{
    nik: string;
    nama_lengkap: string;
}>();
</script>

<template>
    <Head title="Pendaftaran Berhasil" />

    <div class="flex min-h-screen items-center justify-center bg-linear-to-b from-cyan-50 via-slate-100 to-slate-200 p-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-center gap-3 bg-linear-to-r from-cyan-600 to-cyan-500 px-6 py-6 text-white">
                <CheckCircle2 class="h-8 w-8 text-cyan-100" />
                <h1 class="text-xl font-bold sm:text-2xl">Pendaftaran Berhasil!</h1>
            </div>

            <div class="p-6 text-center sm:p-8">
                <p class="mb-6 leading-relaxed text-slate-600">
                    Terima kasih, <strong class="text-slate-800">{{ nama_lengkap }}</strong
                    ><br />
                    <span class="text-sm">(NIK: {{ nik }})</span><br /><br />
                    <strong class="text-cyan-700">Pra Registrasi anda telah berhasil.</strong>
                </p>

                <p class="mb-8 text-sm text-slate-500">
                    Langkah berikutnya, bagi yang belum memiliki akun mitra BPS, silahkan melanjutkan registrasi ke
                    <a href="https://mitra.bps.go.id" target="_blank" class="font-semibold text-cyan-600 underline hover:text-cyan-700">Sobat BPS</a>
                </p>

                <div class="mt-8">
                    <Link
                        href="/"
                        class="flex w-full items-center justify-center rounded-lg bg-cyan-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-cyan-700"
                    >
                        Kembali ke Halaman Utama
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
