<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { media } from '@/lib/media';
import BelumSobatStatusView from '@/components/common/public-status/BelumSobatStatusView.vue';
import BelumSobatStatusNoUploadView from '@/components/common/public-status/BelumSobatStatusNoUploadView.vue';
import SudahBelumWawancaraStatusView from '@/components/common/public-status/SudahBelumWawancaraStatusView.vue';
import StatusDiprosesView from '@/components/common/public-status/StatusDiprosesView.vue';

defineOptions({
    // @ts-ignore
    layout: null,
});

const props = defineProps<{
    mitra: {
        nik: string;
        kode_akses?: string | null;
        nama_lengkap: string;
        posisi_dilamar: string;
        status_sobat: 'Sudah' | 'Belum';
        status_wawancara: 'Belum Wawancara' | 'Sudah Wawancara';
        status_kelulusan: string;
        [key: string]: any;
    };
    uploadSobatExists: boolean;
}>();
const page = usePage();
const selectedSobatFile = ref<File | null>(null);
const isUploadingSobat = ref(false);
const uploadError = ref('');
const mitraRegistrationUrl = 'https://mitra.bps.go.id';
const adminWaUrl = 'https://wa.me/6282144406055';
const isBelumSobat = computed(() => props.mitra.status_sobat === 'Belum');
const isSudahBelumWawancara = computed(() => props.mitra.status_sobat === 'Sudah' && props.mitra.status_wawancara === 'Belum Wawancara');
const selectionUrl = computed(() => (props.mitra.kode_akses ? `/seleksi/${props.mitra.kode_akses}` : null));
const flashSuccess = computed(() => (page.props.flash as { success?: string } | undefined)?.success ?? '');
const serverUploadError = computed(() => (page.props.errors as Record<string, string> | undefined)?.upload_sobat_file ?? '');

function onSelectSobatFile(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    selectedSobatFile.value = file;
    uploadError.value = '';
}

function submitSobatUpload(): void {
    uploadError.value = '';

    if (!selectedSobatFile.value) {
        uploadError.value = 'Silahkan pilih file terlebih dahulu.';
        return;
    }

    const formData = new FormData();
    formData.append('upload_sobat_file', selectedSobatFile.value);

    isUploadingSobat.value = true;
    router.post('/status/upload-sobat', formData, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            isUploadingSobat.value = false;
        },
    });
}
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

                            <!--
                            <BelumSobatStatusView
                                v-if="isBelumSobat"
                                :mitra-registration-url="mitraRegistrationUrl"
                                :media-base="media"
                                :selected-sobat-file="selectedSobatFile"
                                :upload-sobat-exists="uploadSobatExists"
                                :upload-error="uploadError"
                                :server-upload-error="serverUploadError"
                                :flash-success="flashSuccess"
                                :is-uploading-sobat="isUploadingSobat"
                                @select-sobat-file="onSelectSobatFile"
                                @submit-sobat-upload="submitSobatUpload"
                            />
                            -->
                            <BelumSobatStatusNoUploadView
                                v-if="isBelumSobat"
                                :mitra-registration-url="mitraRegistrationUrl"
                                :media-base="media"
                                :selected-sobat-file="selectedSobatFile"
                                :upload-sobat-exists="uploadSobatExists"
                                :upload-error="uploadError"
                                :server-upload-error="serverUploadError"
                                :flash-success="flashSuccess"
                                :is-uploading-sobat="isUploadingSobat"
                                @select-sobat-file="onSelectSobatFile"
                                @submit-sobat-upload="submitSobatUpload"
                            />
                            <SudahBelumWawancaraStatusView v-else-if="isSudahBelumWawancara" :selection-url="selectionUrl" />
                            <StatusDiprosesView v-else />
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
