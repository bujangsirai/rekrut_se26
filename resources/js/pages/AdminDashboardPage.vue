<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';

defineProps<{
    user?: {
        id?: number | null;
        username?: string | null;
    } | null;
    dashboardStats: {
        total_pendaftar: number;
        total_mitra_kepka: number;
        total_mitra_lulus: number;
        total_sobat_sudah: number;
        total_wawancara_sudah: number;
        domisili_summary: Array<{
            kode_kec_dom: string | null;
            nama_kec_dom: string | null;
            total_pendaftar: number;
            total_mitra_kepka: number;
            total_mitra_lulus: number;
            total_sobat_sudah: number;
            total_wawancara_sudah: number;
            desa: Array<{
                kode_desa_dom: string | null;
                nama_desa_dom: string | null;
                total_pendaftar: number;
                total_mitra_kepka: number;
                total_mitra_lulus: number;
                total_sobat_sudah: number;
                total_wawancara_sudah: number;
            }>;
        }>;
    };
}>();

defineOptions({
    layout: LayoutAdmin,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="mx-auto max-w-5xl space-y-5">
        <header class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard Pendaftaran</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Halo, <strong>{{ user?.username ?? 'Pengguna' }}</strong
                    >.
                </p>
            </div>
        </header>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">Jumlah Pendaftar</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ dashboardStats.total_pendaftar }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">Sobat</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ dashboardStats.total_sobat_sudah }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">Kepka</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ dashboardStats.total_mitra_kepka }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">Wawancara</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ dashboardStats.total_wawancara_sudah }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">Lulus</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ dashboardStats.total_mitra_lulus }}</p>
            </article>
        </section>

        <section class="space-y-4">
            <div class="px-1">
                <h2 class="text-lg font-semibold text-slate-900"></h2>
                <p class="mt-1 text-sm text-slate-600">Rekap pendaftar berdasarkan kecamatan</p>
            </div>

            <div v-if="dashboardStats.domisili_summary.length" class="space-y-4">
                <Accordion type="multiple" class="grid gap-3 lg:grid-cols-2">
                    <AccordionItem
                        v-for="kecamatan in dashboardStats.domisili_summary"
                        :key="kecamatan.kode_kec_dom ?? '__NULL_KEC__'"
                        :value="kecamatan.kode_kec_dom ?? '__NULL_KEC__'"
                        class="rounded-xl border border-slate-200 bg-white px-4 shadow-sm"
                    >
                        <AccordionTrigger class="py-3 hover:no-underline">
                            <div class="flex w-full items-center justify-between gap-3">
                                <h3 class="text-xs font-semibold text-slate-900 sm:text-sm">
                                    {{ kecamatan.nama_kec_dom ?? '-' }}
                                </h3>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] font-semibold text-slate-700">
                                        {{ kecamatan.total_pendaftar }} pendaftar
                                    </span>
                                    <span class="rounded-md bg-cyan-100 px-1.5 py-0.5 text-[11px] font-semibold text-cyan-700">
                                        {{ kecamatan.total_sobat_sudah }} sobat
                                    </span>
                                    <span class="rounded-md bg-blue-100 px-1.5 py-0.5 text-[11px] font-semibold text-blue-700">
                                        {{ kecamatan.total_mitra_kepka }} kepka
                                    </span>
                                    <span class="rounded-md bg-amber-100 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700">
                                        {{ kecamatan.total_wawancara_sudah }} wawancara
                                    </span>
                                    <span class="rounded-md bg-emerald-100 px-1.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                                        {{ kecamatan.total_mitra_lulus }} lulus
                                    </span>
                                </div>
                            </div>
                        </AccordionTrigger>

                        <AccordionContent class="pb-3">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs sm:text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-left text-slate-500">
                                            <th class="py-1.5 pr-2 font-medium">desa</th>
                                            <th class="py-1.5 pr-2 font-medium">pendaftar</th>
                                            <th class="py-1.5 pr-2 font-medium">sobat</th>
                                            <th class="py-1.5 pr-2 font-medium">kepka</th>
                                            <th class="py-1.5 pr-2 font-medium">wawancara</th>
                                            <th class="py-1.5 font-medium">lulus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="desa in kecamatan.desa"
                                            :key="`${kecamatan.kode_kec_dom ?? '__NULL_KEC__'}-${desa.kode_desa_dom ?? '__NULL_DESA__'}`"
                                            class="border-b border-slate-100 last:border-b-0"
                                        >
                                            <td class="py-1.5 pr-2 text-slate-800">
                                                {{ desa.nama_desa_dom ?? '-' }}
                                            </td>
                                            <td class="py-1.5 pr-2 font-medium text-slate-900">{{ desa.total_pendaftar }}</td>
                                            <td class="py-1.5 pr-2 font-medium text-cyan-700">{{ desa.total_sobat_sudah }}</td>
                                            <td class="py-1.5 pr-2 font-medium text-blue-700">{{ desa.total_mitra_kepka }}</td>
                                            <td class="py-1.5 pr-2 font-medium text-amber-700">{{ desa.total_wawancara_sudah }}</td>
                                            <td class="py-1.5 font-medium text-emerald-700">{{ desa.total_mitra_lulus }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>

            <p v-else class="rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-500 shadow-sm">Belum ada data pendaftar.</p>
        </section>
    </div>
</template>
