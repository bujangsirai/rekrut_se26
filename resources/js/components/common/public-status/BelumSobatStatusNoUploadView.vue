<script setup lang="ts">
defineProps<{
    mitraRegistrationUrl: string;
    mediaBase: string;
    selectedSobatFile: File | null;
    uploadSobatExists: boolean;
    uploadError: string;
    serverUploadError: string;
    flashSuccess: string;
    isUploadingSobat: boolean;
}>();

defineEmits<{
    selectSobatFile: [event: Event];
    submitSobatUpload: [];
}>();
</script>

<template>
    <div class="space-y-2">
        <span class="block text-xs font-semibold tracking-wider text-slate-500 uppercase">Status Sekarang</span>
        <span class="inline-flex items-center rounded-full bg-red-100 px-4 py-1.5 text-center text-sm font-semibold text-red-800">
            Belum terdaftar di sensus/survei pada mitra.bps.go.id &nbsp;
            <a
                :href="mediaBase + 'img/contoh/daftar_survei.png'"
                target="_blank"
                rel="noopener noreferrer"
                class="text-xs text-cyan-600 underline-offset-2 hover:underline"
            >
                (contoh)
            </a>
        </span>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">
            Selain Mitra KEPKA BPS 2026, Silahkan lanjutkan pendaftaran di
            <a
                :href="mitraRegistrationUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="font-semibold text-cyan-700 underline decoration-cyan-300 underline-offset-2 hover:text-cyan-800"
            >
                mitra.bps.go.id
            </a>
            .
        </p>
        <p class="text-xs font-semibold text-red-700">MOHON MAAF PENDAFTARAN DITUTUP</p>
    </div>
</template>
