<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({
    // @ts-ignore
    layout: null,
});

defineProps<{
    mitra: {
        nik: string;
        nama_lengkap: string;
        posisi_dilamar: string;
        status_kelulusan: string;
        [key: string]: any;
    };
}>();
</script>

<template>
    <Head title="Status Pendaftaran" />

    <div class="min-h-screen bg-linear-to-b from-cyan-50 via-slate-100 to-slate-200 px-2 py-5 sm:px-6 sm:py-10 lg:py-12">
        <div
            class="mx-auto flex min-h-[calc(100vh-2.5rem)] w-full max-w-4xl items-center sm:min-h-[calc(100vh-5rem)] lg:min-h-[calc(100vh-6rem)]"
        >
            <div class="w-full space-y-4 sm:space-y-6 lg:space-y-8">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-3 py-2.5 sm:px-6 sm:py-4">
                        <h2 class="text-[15px] font-bold leading-snug uppercase tracking-normal text-white sm:text-lg lg:text-xl">
                            Status Pendaftaran: {{ mitra.nama_lengkap }}
                        </h2>
                    </header>

                    <div class="space-y-6 p-4 sm:p-8">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor NIK</span>
                                <p class="text-lg font-bold text-slate-900">{{ mitra.nik }}</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Posisi Dilamar</span>
                                <p class="text-lg font-bold text-slate-900">{{ mitra.posisi_dilamar }}</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Kelulusan</span>
                                <div>
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-3 py-1 text-sm font-bold uppercase tracking-wide',
                                            mitra.status_kelulusan === 'Lulus' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700',
                                        ]"
                                    >
                                        {{ mitra.status_kelulusan }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 pt-6">
                            <h3 class="mb-4 text-base font-bold text-slate-900">Informasi Tambahan</h3>
                            <div class="space-y-4 text-sm leading-relaxed text-slate-600 sm:text-base">
                                <p>
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et
                                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                                    ex ea commodo consequat.
                                </p>
                                <p>
                                    Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                                    Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est
                                    laborum.
                                </p>
                                <p>
                                    Curabitur pretium tincidunt lacus. Nulla gravida orci a odio. Nullam varius, turpis et commodo pharetra,
                                    est eros bibendum elit, nec luctus magna felis sollicitudin mauris. Integer in mauris eu nibh euismod
                                    gravida.
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-6">
                            <Link
                                href="/"
                                class="inline-flex h-11 items-center justify-center rounded-xl bg-slate-800 px-6 text-sm font-semibold text-white transition hover:bg-slate-900"
                            >
                                Kembali Ke Beranda
                            </Link>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
