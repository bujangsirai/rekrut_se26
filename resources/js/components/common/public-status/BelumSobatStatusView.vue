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

const emit = defineEmits<{
    selectSobatFile: [event: Event];
    submitSobatUpload: [];
}>();
</script>

<template>
    <div class="space-y-2">
        <span class="block text-xs font-semibold tracking-wider text-slate-500 uppercase">Status Sekarang</span>
        <span class="inline-flex items-center rounded-full bg-red-100 px-4 py-1.5 text-center text-sm font-semibold text-red-800">
            Belum terdaftar di mitra.bps.go.id
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

            lalu upload bukti screenshot nya ke sini. jika mitra KEPKA, silahkan upload bukti penerimaan penawaran
        </p>
    </div>

    <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4 text-left">
        <div class="flex items-center gap-1">
            <p class="text-sm font-semibold text-slate-800">Silahkan upload bukti pendaftaran/penerimaan penawaran</p>
            <a
                :href="mediaBase + 'img/contoh/daftar_survei.png'"
                target="_blank"
                rel="noopener noreferrer"
                class="text-xs text-cyan-600 underline-offset-2 hover:underline"
            >
                (contoh)
            </a>
        </div>

        <div class="mt-3 space-y-2">
            <div class="relative">
                <input
                    type="file"
                    accept=".pdf,image/png,image/jpeg,image/jpg,image/webp"
                    class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                    @change="emit('selectSobatFile', $event)"
                />
                <div class="flex w-full items-center rounded-md border border-slate-300 bg-white text-sm">
                    <div class="m-[3px] cursor-pointer rounded-md bg-cyan-50 px-3 py-1.5 text-sm font-semibold text-cyan-700">Pilih File</div>
                    <span class="truncate px-2" :class="selectedSobatFile ? 'text-slate-900' : 'text-slate-500'">
                        {{ selectedSobatFile?.name ?? 'Belum ada file yang dipilih' }}
                    </span>
                </div>
            </div>

            <p class="text-xs text-slate-500">Format: pdf/jpg/jpeg/png/webp. Maksimal 2MB.</p>
            <p v-if="uploadSobatExists" class="text-xs font-medium text-emerald-700">
                Bukti upload sobat sudah tersimpan. Upload ulang akan mengganti file sebelumnya.
            </p>
            <p v-if="uploadError" class="text-xs font-medium text-red-600">{{ uploadError }}</p>
            <p v-if="serverUploadError" class="text-xs font-medium text-red-600">{{ serverUploadError }}</p>
            <p v-if="flashSuccess" class="text-xs font-medium text-emerald-700">{{ flashSuccess }}</p>
            <p class="text-xs font-semibold text-red-700">MOHON MAAF ANDA TIDAK BISA UPLOAD FILE LAGI</p>

            <button
                type="button"
                class="inline-flex h-9 items-center justify-center rounded-md bg-cyan-600 px-4 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="true"
                @click="emit('submitSobatUpload')"
            >
                {{ isUploadingSobat ? 'Mengunggah...' : 'Upload Bukti' }}
            </button>
        </div>
    </div>
</template>
