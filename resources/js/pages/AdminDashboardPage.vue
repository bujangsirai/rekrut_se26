<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';

defineProps<{
    user?: {
        id?: number | null;
        username?: string | null;
    } | null;
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
                    <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Area Organik</p>
                    <h1 class="text-2xl font-bold text-slate-900">Admin Dashboard</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Halo, <strong>{{ user?.username ?? 'Pengguna' }}</strong>. Halaman ini hanya bisa diakses setelah login.
                    </p>
                </div>
            </header>

            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Contoh Menu /admin/{xxx}</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Mulai dari sini, semua halaman internal organik bisa diletakkan di prefix
                    <code class="rounded bg-slate-100 px-1 py-0.5">/admin/*</code>.
                </p>
            </section>
        </div>
</template>
