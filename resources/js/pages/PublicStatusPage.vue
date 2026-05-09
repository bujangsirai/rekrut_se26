<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({
    // @ts-ignore
    layout: null,
});

const props = defineProps<{
    mitra: {
        nik: string;
        nama_lengkap: string;
        posisi_dilamar: string;
        status_sobat: 'Sudah' | 'Belum';
        status_wawancara: 'Belum Wawancara' | 'Sudah Wawancara';
        status_kelulusan: string;
        [key: string]: any;
    };
}>();
const mitraRegistrationUrl = 'https://mitra.bps.go.id';
const adminWaUrl = 'https://wa.me/6282144406055';
const isBelumSobat = computed(() => props.mitra.status_sobat === 'Belum');

const publicStatusContent = computed(() => {
    if (props.mitra.status_sobat === 'Belum') {
        return {
            badge: 'Belum terdaftar di mitra.bps.go.id',
            detail: 'Silahkan lanjutkan pendaftaran, jika merasa sudah mendaftar namun status di halaman ini belum berubah, silahkan hubungi admin',
        };
    }

    if (props.mitra.status_sobat === 'Sudah' && props.mitra.status_wawancara === 'Belum Wawancara') {
        return {
            badge: 'Menunggu Tahap Berikutnya',
            detail: 'Silahkan tunggu tahap berikutnya ya',
        };
    }

    return {
        badge: 'Status Diproses',
        detail: 'Silahkan menunggu tahapan berikutnya',
    };
});

const publicStatusClass = computed(() => {
    return props.mitra.status_sobat === 'Belum'
        ? 'bg-red-100 text-red-800'
        : 'bg-cyan-100 text-cyan-800';
});
</script>

<template>
    <Head title="Status Pendaftaran" />

    <div class="min-h-screen bg-linear-to-b from-cyan-50 via-slate-100 to-slate-200 px-2 py-5 sm:px-6 sm:py-10 lg:py-12">
        <div class="mx-auto flex min-h-[calc(100vh-2.5rem)] w-full max-w-xl items-center sm:min-h-[calc(100vh-5rem)] lg:min-h-[calc(100vh-6rem)]">
            <div class="w-full space-y-4 sm:space-y-6 lg:space-y-8">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-3 py-2.5 sm:px-6 sm:py-4">
                        <h2 class="text-center text-[15px] leading-snug font-bold tracking-normal text-white uppercase sm:text-lg lg:text-xl">
                            STATUS PENDAFTARAN
                        </h2>
                    </header>

                    <div class="p-6 text-center sm:p-8">
                        <div class="space-y-6">
                            <div class="space-y-1">
                                <span class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Nama Lengkap</span>
                                <p class="text-xl font-bold text-slate-900">{{ mitra.nama_lengkap }}</p>
                            </div>

                            <div class="space-y-2">
                                <span class="block text-xs font-semibold tracking-wider text-slate-500 uppercase">Status Sekarang</span>
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full px-4 py-1.5 text-center text-sm font-semibold',
                                        publicStatusClass,
                                    ]"
                                >
                                    {{ publicStatusContent.badge }}
                                </span>
                                <p v-if="isBelumSobat" class="mt-2 text-sm leading-relaxed text-slate-600">
                                    Silahkan lanjutkan pendaftaran di
                                    <a
                                        :href="mitraRegistrationUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="font-semibold text-cyan-700 underline decoration-cyan-300 underline-offset-2 hover:text-cyan-800"
                                    >
                                        mitra.bps.go.id
                                    </a>
                                    , jika merasa sudah mendaftar namun status di halaman ini belum berubah, silahkan hubungi WA admin
                                </p>
                                <p v-else class="mt-2 text-sm leading-relaxed text-slate-600">
                                    {{ publicStatusContent.detail }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-center">
                            <Link
                                href="/"
                                class="flex w-full items-center justify-center rounded-lg bg-cyan-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-cyan-700"
                            >
                                Kembali ke Halaman Utama
                            </Link>
                        </div>
                        <p class="mt-3 text-xs text-slate-600">
                            Punya pertanyaan? silahakn hubungi WA admin 0821 4440 6055 atau
                            <a
                                :href="adminWaUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="font-semibold text-cyan-700 underline decoration-cyan-300 underline-offset-2 hover:text-cyan-800"
                            >
                                klik disini
                            </a>
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
