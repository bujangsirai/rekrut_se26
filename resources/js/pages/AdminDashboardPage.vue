<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';

defineProps<{
    user?: {
        id?: number | null;
        username?: string | null;
    } | null;
    dashboardStats: {
        total_pendaftar: number;
        domisili_summary: Array<{
            kode_kec_dom: string | null;
            nama_kec_dom: string | null;
            total: number;
            desa: Array<{
                kode_desa_dom: string | null;
                nama_desa_dom: string | null;
                total: number;
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
                    <h1 class="text-2xl font-bold text-slate-900">Admin Dashboard</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Halo, <strong>{{ user?.username ?? 'Pengguna' }}</strong>. Halaman ini hanya bisa diakses setelah login.
                    </p>
                </div>
            </header>

            <section class="grid gap-4 md:grid-cols-2">
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-600">Jumlah Pendaftar</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ dashboardStats.total_pendaftar }}</p>
                </article>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Summary Domisili Pendaftar</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Rekap berdasarkan kecamatan dan desa domisili.
                </p>

                <div v-if="dashboardStats.domisili_summary.length" class="mt-4 space-y-3">
                    <article
                        v-for="kecamatan in dashboardStats.domisili_summary"
                        :key="kecamatan.kode_kec_dom ?? '__NULL_KEC__'"
                        class="rounded-lg border border-slate-200 p-4"
                    >
                        <header class="flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900">
                                Kecamatan Domisili:
                                <span class="font-bold">
                                    {{ kecamatan.nama_kec_dom ?? '-' }}
                                    ({{ kecamatan.kode_kec_dom ?? '-' }})
                                </span>
                            </h3>
                            <span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                {{ kecamatan.total }} pendaftar
                            </span>
                        </header>

                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="py-2 pr-3 font-medium">Desa Domisili</th>
                                        <th class="py-2 font-medium">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="desa in kecamatan.desa"
                                        :key="`${kecamatan.kode_kec_dom ?? '__NULL_KEC__'}-${desa.kode_desa_dom ?? '__NULL_DESA__'}`"
                                        class="border-b border-slate-100 last:border-b-0"
                                    >
                                        <td class="py-2 pr-3 text-slate-800">
                                            {{ desa.nama_desa_dom ?? '-' }}
                                            ({{ desa.kode_desa_dom ?? '-' }})
                                        </td>
                                        <td class="py-2 font-medium text-slate-900">{{ desa.total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </div>

                <p v-else class="mt-4 text-sm text-slate-500">Belum ada data pendaftar.</p>
            </section>
        </div>
</template>
