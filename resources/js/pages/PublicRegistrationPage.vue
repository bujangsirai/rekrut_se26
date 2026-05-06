<script setup lang="ts">
import { TanStackCombobox, TanStackInput, TanStackSelect, TanStackTextarea } from '@/components/ui/form';
import { Head, Link, router } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

defineOptions({
    // @ts-ignore
    layout: null,
});

const props = defineProps<{
    kecamatanOptions: Array<{
        kode_kec: string;
        nama_kec: string;
    }>;
    desaOptions: Array<{
        kode_kec: string;
        kode_desa: string;
        nama_desa: string;
    }>;
}>();

const firstKecamatanCode = props.kecamatanOptions[0]?.kode_kec ?? '';
const firstDesaCode = props.desaOptions.find((item) => item.kode_desa.slice(0, 7) === firstKecamatanCode)?.kode_desa ?? '';

const kecamatanSelectOptions = computed(() =>
    props.kecamatanOptions.map((item) => ({
        label: item.nama_kec,
        value: item.kode_kec,
    })),
);

const form = useForm({
    defaultValues: {
        nik: '',
        nama_lengkap: '',
        email: '',
        jenis_kelamin: 'Laki-laki',
        kode_kec: firstKecamatanCode,
        kode_desa: firstDesaCode,
        alamat_lengkap: '',
        tanggal_lahir: '',
        tempat_lahir: '',
        status_perkawinan: 'Belum Kawin',
        pendidikan_terakhir: 'SLTP/Kebawah',
        pekerjaan: '',
        nomor_whatsapp: '',
        riwayat_kegiatan_bps: '',
        ktp_file: null as File | null,
    },
    onSubmit: async ({ value }) =>
        new Promise<void>((resolve) => {
            const payload = new FormData();
            payload.append('nik', value.nik);
            payload.append('nama_lengkap', value.nama_lengkap);
            payload.append('email', value.email);
            payload.append('jenis_kelamin', value.jenis_kelamin);
            payload.append('kode_kec', value.kode_kec);
            payload.append('kode_desa', value.kode_desa);
            payload.append('alamat_lengkap', value.alamat_lengkap);
            payload.append('tanggal_lahir', value.tanggal_lahir);
            payload.append('tempat_lahir', value.tempat_lahir);
            payload.append('status_perkawinan', value.status_perkawinan);
            payload.append('pendidikan_terakhir', value.pendidikan_terakhir);
            payload.append('pekerjaan', value.pekerjaan);
            payload.append('nomor_whatsapp', value.nomor_whatsapp);
            payload.append('riwayat_kegiatan_bps', value.riwayat_kegiatan_bps ?? '');

            if (value.ktp_file) {
                payload.append('ktp_file', value.ktp_file);
            }

            router.post('/daftar', payload, {
                forceFormData: true,
                preserveScroll: true,
                onFinish: () => resolve(),
            });
        }),
});

const values = form.useStore((state) => state.values);
const isSubmitting = form.useStore((state) => state.isSubmitting);
const temporaryKtpPreviewUrl = ref('');

const desaSelectOptions = computed(() => {
    if (!values.value.kode_kec) {
        return [];
    }

    return props.desaOptions
        .filter((item) => item.kode_desa.slice(0, 7) === values.value.kode_kec)
        .map((item) => ({
            label: item.nama_desa,
            value: item.kode_desa,
        }));
});

watch(
    () => values.value.kode_kec,
    (kodeKec) => {
        if (!kodeKec) {
            form.setFieldValue('kode_desa', '');

            return;
        }

        const firstDesa = props.desaOptions.find((item) => item.kode_desa.slice(0, 7) === kodeKec)?.kode_desa ?? '';
        form.setFieldValue('kode_desa', firstDesa);
    },
);

watch(
    () => values.value.ktp_file,
    (file) => {
        if (temporaryKtpPreviewUrl.value) {
            URL.revokeObjectURL(temporaryKtpPreviewUrl.value);
            temporaryKtpPreviewUrl.value = '';
        }

        if (file instanceof File) {
            temporaryKtpPreviewUrl.value = URL.createObjectURL(file);
        }
    },
);

onBeforeUnmount(() => {
    if (temporaryKtpPreviewUrl.value) {
        URL.revokeObjectURL(temporaryKtpPreviewUrl.value);
    }
});
</script>

<template>
    <Head title="Form Pendaftaran Mitra" />

    <div class="min-h-screen bg-linear-to-b from-cyan-50 via-slate-100 to-slate-200 px-2 py-3 sm:px-6 sm:py-6">
        <div class="mx-auto max-w-5xl space-y-4">
            <header class="rounded-xl border border-cyan-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h1 class="text-lg font-bold text-cyan-800 sm:text-xl">Form Pendaftaran Mitra SE2026</h1>
                    <Link href="/" class="text-sm font-medium text-cyan-700 hover:underline">Kembali</Link>
                </div>
            </header>

            <form class="space-y-4 sm:space-y-5" @submit.prevent="form.handleSubmit">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-4 py-3">
                        <h2 class="text-base font-bold text-white sm:text-lg">I. INFORMASI UMUM</h2>
                    </header>

                    <div class="grid gap-4 p-4 sm:p-6 md:grid-cols-2">
                        <TanStackInput
                            :form="form"
                            name="nik"
                            label="NIK (16 Digit)*"
                            placeholder="Contoh: 5207xxxxxxxxxxxx"
                            :number-only="true"
                            :maxlength="16"
                            :validators="{
                                onSubmit: ({ value }: any) => (!value ? 'NIK wajib diisi' : value.length !== 16 ? 'NIK harus 16 angka' : undefined),
                            }"
                        />
                        <TanStackInput
                            :form="form"
                            name="nama_lengkap"
                            label="Nama Lengkap*"
                            placeholder="Masukkan nama lengkap sesuai KTP"
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Nama lengkap wajib diisi' : undefined) }"
                        />

                        <TanStackCombobox
                            :form="form"
                            name="kode_kec"
                            label="Asal Kecamatan*"
                            :options="kecamatanSelectOptions"
                            placeholder="Cari kecamatan..."
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Kecamatan wajib dipilih' : undefined) }"
                        />
                        <TanStackCombobox
                            :form="form"
                            name="kode_desa"
                            label="Asal Desa*"
                            :options="desaSelectOptions"
                            placeholder="Cari desa..."
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Desa wajib dipilih' : undefined) }"
                        />
                        <TanStackInput
                            :form="form"
                            name="tempat_lahir"
                            label="Tempat Lahir*"
                            placeholder="Contoh: Sumbawa Barat"
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Tempat lahir wajib diisi' : undefined) }"
                        />
                        <TanStackInput
                            :form="form"
                            name="tanggal_lahir"
                            type="date"
                            label="Tanggal Lahir*"
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Tanggal lahir wajib diisi' : undefined) }"
                        />
                        <TanStackSelect
                            :form="form"
                            name="jenis_kelamin"
                            label="Jenis Kelamin*"
                            :options="[
                                { label: 'Laki-laki', value: 'Laki-laki' },
                                { label: 'Perempuan', value: 'Perempuan' },
                            ]"
                        />
                        <TanStackSelect
                            :form="form"
                            name="status_perkawinan"
                            label="Status Perkawinan*"
                            :options="[
                                { label: 'Belum Kawin', value: 'Belum Kawin' },
                                { label: 'Kawin', value: 'Kawin' },
                                { label: 'Cerai Hidup', value: 'Cerai Hidup' },
                                { label: 'Cerai Mati', value: 'Cerai Mati' },
                            ]"
                        />
                        <TanStackSelect
                            :form="form"
                            name="pendidikan_terakhir"
                            label="Pendidikan Terakhir*"
                            :options="[
                                { label: 'SLTP/Kebawah', value: 'SLTP/Kebawah' },
                                { label: 'SLTA', value: 'SLTA' },
                                { label: 'DI/DII/DII', value: 'DI/DII/DII' },
                                { label: 'DIV/S1/S2/S3', value: 'DIV/S1/S2/S3' },
                            ]"
                        />
                        <TanStackInput
                            :form="form"
                            name="pekerjaan"
                            label="Pekerjaan*"
                            placeholder="Contoh: Wiraswasta / Pegawai Swasta"
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Pekerjaan wajib diisi' : undefined) }"
                        />
                        <TanStackInput
                            :form="form"
                            name="nomor_whatsapp"
                            label="Nomor WhatsApp*"
                            placeholder="Contoh: 081234567890"
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Nomor WhatsApp wajib diisi' : undefined) }"
                        />

                        <TanStackInput
                            :form="form"
                            name="email"
                            type="email"
                            label="Alamat Email*"
                            placeholder="Contoh: nama@email.com"
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Email wajib diisi' : undefined) }"
                        />
                        <TanStackInput
                            :form="form"
                            name="alamat_lengkap"
                            label="Alamat Lengkap*"
                            placeholder="Masukkan alamat domisili saat ini"
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Alamat lengkap wajib diisi' : undefined) }"
                        />

                        <div class="md:col-span-2">
                            <TanStackTextarea
                                :form="form"
                                name="riwayat_kegiatan_bps"
                                label="Riwayat Kegiatan BPS"
                                placeholder="Sebutkan kegiatan BPS yang pernah diikuti, dipisahkan dengan tanda koma"
                                :rows="3"
                            />
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-4 py-3">
                        <h2 class="text-base font-bold text-white sm:text-lg">II. DOKUMEN UNGGAHAN</h2>
                    </header>
                    <div class="grid gap-4 p-4 sm:p-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <form.Field
                                name="ktp_file"
                                :validators="{ onSubmit: ({ value }: any) => (!value ? 'File KTP wajib diunggah' : undefined) }"
                            >
                                <template #default="{ field }">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium">Upload KTP*</label>
                                        <div class="relative">
                                            <input
                                                type="file"
                                                accept=".pdf,image/png,image/jpeg,image/jpg,image/webp"
                                                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                                @change="(event: Event) => field.handleChange((event.target as HTMLInputElement).files?.[0] ?? null)"
                                            />
                                            <div class="flex w-full items-center rounded-md border border-slate-300 bg-white text-sm">
                                                <div
                                                    class="m-[3px] cursor-pointer rounded-md bg-cyan-50 px-3 py-1.5 text-sm font-semibold text-cyan-700"
                                                >
                                                    Pilih File
                                                </div>
                                                <span class="truncate px-2" :class="field.state.value ? 'text-slate-900' : 'text-slate-500'">
                                                    {{ field.state.value?.name ?? 'Belum ada file yang dipilih' }}
                                                </span>
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-500">Format: pdf/jpg/jpeg/png. Maksimal 5MB.</p>
                                        <p v-if="field.state.meta.errors.length" class="mt-1 text-xs text-destructive">
                                            {{ field.state.meta.errors.join(', ') }}
                                        </p>
                                        <p v-else-if="$page.props.errors?.ktp_file" class="mt-1 text-xs text-destructive">
                                            {{ $page.props.errors.ktp_file }}
                                        </p>
                                        <a
                                            v-if="temporaryKtpPreviewUrl"
                                            :href="temporaryKtpPreviewUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex text-xs font-medium text-cyan-700 underline underline-offset-2 hover:text-cyan-800"
                                        >
                                            Lihat file KTP yang dipilih
                                        </a>
                                    </div>
                                </template>
                            </form.Field>
                        </div>
                    </div>
                    <div class="flex justify-end px-4 pb-4 sm:px-6 sm:pb-6">
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="inline-flex h-10 cursor-pointer items-center justify-center rounded-lg bg-green-600 px-5 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ isSubmitting ? 'Mengirim...' : 'Kirim Pendaftaran' }}
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </div>
</template>
