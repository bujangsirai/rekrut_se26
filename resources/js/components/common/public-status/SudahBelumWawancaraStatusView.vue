<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    selectionUrl: string | null;
}>();
</script>

<template>
    <div class="space-y-2">
        <span class="block text-xs font-semibold tracking-wider text-slate-500 uppercase">Status Sekarang</span>
        <span class="inline-flex items-center rounded-full bg-cyan-100 px-4 py-1.5 text-center text-sm font-semibold text-cyan-800">
            Pendaftaran Anda Telah Terproses. Silahkan Menunggu Informasi Berikutnya
        </span>
        <span class="mt-2 inline-flex items-center rounded-full bg-cyan-50 px-4 py-1.5 text-center text-sm font-medium text-cyan-700">
            Selanjutnya akan dilanjutkan wawancara, untuk mitra baru jangan lupa melakukan tes kompetensi di mitra.bps.go.id juga.
        </span>
        <div class="pt-1">
            <Link
                v-if="selectionUrl"
                :href="selectionUrl"
                class="inline-flex h-9 items-center justify-center rounded-md bg-cyan-600 px-4 text-sm font-semibold text-white transition hover:bg-cyan-700"
            >
                Lanjut Seleksi
            </Link>
        </div>
    </div>
</template>
