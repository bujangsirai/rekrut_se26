<script setup lang="ts">
import { TanStackCombobox, TanStackInput, TanStackSelect, TanStackTextarea, TanStackCheckbox } from '@/components/ui/form';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { media } from '@/lib/media';
import { toast } from 'vue-sonner';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

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
        is_domksb: false,
        kode_kec_dom: firstKecamatanCode,
        kode_desa_dom: firstDesaCode,
        tanggal_lahir: '',
        tempat_lahir: '',
        status_perkawinan: 'Belum Kawin',
        pendidikan_terakhir: 'SLTP/Kebawah',
        pekerjaan: '',
        is_not_asn: false,
        is_not_hamil: true,
        is_motor: false,
        nomor_whatsapp: '',
        merk_hp: '',
        riwayat_kegiatan_bps: '',
        ktp_file: null as File | null,
        spek_hp_file: null as File | null,
        follow_ig_file: null as File | null,
    },
    onSubmit: async () => {
        isConfirmDialogOpen.value = true;
    },
});

const isConfirmDialogOpen = ref(false);

const performSubmission = () => {
    isConfirmDialogOpen.value = false;
    const value = values.value;
    console.log('SENDING DATA:', { ...value });

    const payload = new FormData();
    payload.append('nik', value.nik);
    payload.append('nama_lengkap', value.nama_lengkap);
    payload.append('email', value.email);
    payload.append('jenis_kelamin', value.jenis_kelamin);
    payload.append('kode_kec', value.kode_kec);
    payload.append('kode_desa', value.kode_desa);
    payload.append('alamat_lengkap', value.alamat_lengkap);
    payload.append('is_domksb', value.is_domksb ? '1' : '0');
    payload.append('kode_kec_dom', value.kode_kec_dom ?? '');
    payload.append('kode_desa_dom', value.kode_desa_dom ?? '');
    payload.append('tanggal_lahir', value.tanggal_lahir);
    payload.append('tempat_lahir', value.tempat_lahir);
    payload.append('status_perkawinan', value.status_perkawinan);
    payload.append('pendidikan_terakhir', value.pendidikan_terakhir);
    payload.append('pekerjaan', value.pekerjaan);
    payload.append('is_not_asn', value.is_not_asn ? '1' : '0');
    payload.append('is_not_hamil', value.is_not_hamil ? '1' : '0');
    payload.append('is_motor', value.is_motor ? '1' : '0');
    payload.append('nomor_whatsapp', value.nomor_whatsapp);
    payload.append('merk_hp', value.merk_hp);
    payload.append('riwayat_kegiatan_bps', value.riwayat_kegiatan_bps ?? '');

    if (value.ktp_file) {
        payload.append('ktp_file', value.ktp_file);
    }
    if (value.spek_hp_file) {
        payload.append('spek_hp_file', value.spek_hp_file);
    }
    if (value.follow_ig_file) {
        payload.append('follow_ig_file', value.follow_ig_file);
    }

    router.post('/daftar_yaaa', payload, {
        forceFormData: true,
        preserveScroll: true,
    });
};

const values = form.useStore((state) => state.values);
const isSubmitting = form.useStore((state) => state.isSubmitting);

// Independent Watchers for KTP and Domisili Fields
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
    () => values.value.kode_kec_dom,
    (kodeKecDom) => {
        if (!kodeKecDom) {
            form.setFieldValue('kode_desa_dom', '');
            return;
        }

        const firstDesa = props.desaOptions.find((item) => item.kode_desa.slice(0, 7) === kodeKecDom)?.kode_desa ?? '';
        form.setFieldValue('kode_desa_dom', firstDesa);
    },
);

const handleFormSubmit = async (e: Event) => {
    e.preventDefault();
    console.log('FORM DATA:', { ...values.value });
    await form.handleSubmit();

    // Check for client-side validation errors
    const state = form.state;
    const hasErrors = state.fieldMeta && Object.values(state.fieldMeta).some((meta: any) => meta.errors.length > 0);

    if (hasErrors) {
        toast.error('Pendaftaran tidak dapat dikirim. Mohon lengkapi semua data yang wajib diisi.');
    }
};

const temporaryKtpPreviewUrl = ref('');
const temporarySpekHpPreviewUrl = ref('');
const temporaryFollowIgPreviewUrl = ref('');

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

const desaDomSelectOptions = computed(() => {
    if (!values.value.kode_kec_dom) {
        return [];
    }

    return props.desaOptions
        .filter((item) => item.kode_desa.slice(0, 7) === values.value.kode_kec_dom)
        .map((item) => ({
            label: item.nama_desa,
            value: item.kode_desa,
        }));
});

watch(
    () => values.value.kode_kec_dom,
    (kodeKecDom) => {
        if (!kodeKecDom) {
            form.setFieldValue('kode_desa_dom', '');

            return;
        }

        const firstDesa = props.desaOptions.find((item) => item.kode_desa.slice(0, 7) === kodeKecDom)?.kode_desa ?? '';
        form.setFieldValue('kode_desa_dom', firstDesa);
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

watch(
    () => values.value.spek_hp_file,
    (file) => {
        if (temporarySpekHpPreviewUrl.value) {
            URL.revokeObjectURL(temporarySpekHpPreviewUrl.value);
            temporarySpekHpPreviewUrl.value = '';
        }

        if (file instanceof File) {
            temporarySpekHpPreviewUrl.value = URL.createObjectURL(file);
        }
    },
);

watch(
    () => values.value.follow_ig_file,
    (file) => {
        if (temporaryFollowIgPreviewUrl.value) {
            URL.revokeObjectURL(temporaryFollowIgPreviewUrl.value);
            temporaryFollowIgPreviewUrl.value = '';
        }

        if (file instanceof File) {
            temporaryFollowIgPreviewUrl.value = URL.createObjectURL(file);
        }
    },
);

onBeforeUnmount(() => {
    if (temporaryKtpPreviewUrl.value) {
        URL.revokeObjectURL(temporaryKtpPreviewUrl.value);
    }
    if (temporarySpekHpPreviewUrl.value) {
        URL.revokeObjectURL(temporarySpekHpPreviewUrl.value);
    }
    if (temporaryFollowIgPreviewUrl.value) {
        URL.revokeObjectURL(temporaryFollowIgPreviewUrl.value);
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

            <form class="space-y-4 sm:space-y-5" @submit.prevent="handleFormSubmit">
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
                            placeholder="Contoh: Satpam di Puskesmas Taliwang, Sopir di BNI"
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Pekerjaan wajib diisi' : undefined) }"
                        />

                        <div class="mt-4 md:col-span-2"></div>
                        <TanStackCombobox
                            :form="form"
                            name="kode_kec"
                            label="Kecamatan (Sesuai KTP)*"
                            :options="kecamatanSelectOptions"
                            placeholder="Cari kecamatan..."
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Kecamatan wajib dipilih' : undefined) }"
                        />
                        <TanStackCombobox
                            :form="form"
                            name="kode_desa"
                            label="Desa (Sesuai KTP)*"
                            :options="desaSelectOptions"
                            placeholder="Cari desa..."
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Desa wajib dipilih' : undefined) }"
                        />

                        <TanStackCombobox
                            :form="form"
                            name="kode_kec_dom"
                            label="Kecamatan Domisili*"
                            :options="kecamatanSelectOptions"
                            placeholder="Cari kecamatan domisili..."
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Kecamatan domisili wajib dipilih' : undefined) }"
                        />
                        <TanStackCombobox
                            :form="form"
                            name="kode_desa_dom"
                            label="Desa Domisili*"
                            :options="desaDomSelectOptions"
                            placeholder="Cari desa domisili..."
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Desa domisili wajib dipilih' : undefined) }"
                        />
                        <div class="md:col-span-2">
                            <TanStackInput
                                :form="form"
                                name="alamat_lengkap"
                                label="Alamat Lengkap (Sesuai Domisili)*"
                                placeholder="Masukkan alamat domisili saat ini"
                                :validators="{ onSubmit: ({ value }: any) => (!value ? 'Alamat lengkap wajib diisi' : undefined) }"
                            />
                        </div>

                        <div class="mt-4 md:col-span-2"></div>
                        <TanStackInput
                            :form="form"
                            name="nomor_whatsapp"
                            label="Nomor WhatsApp*"
                            placeholder="Contoh: 081234567890"
                            :phone-only="true"
                            :validators="{
                                onSubmit: ({ value }: any) => {
                                    if (!value) return 'Nomor WhatsApp wajib diisi';
                                    if (!/^(08|\+62)\d+$/.test(value)) return 'Format salah (harus diawali 08 atau +62, dan hanya angka)';
                                    return undefined;
                                },
                            }"
                        />
                        <TanStackInput
                            :form="form"
                            name="email"
                            type="email"
                            label="Alamat Email*"
                            placeholder="Contoh: nama@email.com"
                            :validators="{ onSubmit: ({ value }: any) => (!value ? 'Email wajib diisi' : undefined) }"
                        />
                        <div class="md:col-span-2">
                            <TanStackInput
                                :form="form"
                                name="merk_hp"
                                label="Merek Handphone*"
                                placeholder="Contoh: Samsung Galaxy A54"
                                :validators="{ onSubmit: ({ value }: any) => (!value ? 'Merek Handphone wajib diisi' : undefined) }"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <TanStackTextarea
                                :form="form"
                                name="riwayat_kegiatan_bps"
                                label="Riwayat Kegiatan Sensus/Survei"
                                placeholder="Sebutkan semua kegiatan survei/sensus yang pernah diikuti (BPS/Instansi lain) dalam rentang tahun 2020-2026"
                                :rows="3"
                            />
                        </div>
                        <div class="mt-1 md:col-span-2"></div>
                        <div class="flex flex-col gap-3 md:col-span-2">
                            <TanStackCheckbox :form="form" name="is_domksb" label="Saya berdomisili saat ini di Sumbawa Barat" />
                            <TanStackCheckbox :form="form" name="is_not_asn" label="Saya bukan Aparatur Sipil Negara/TNI/Polri" />
                            <TanStackCheckbox
                                :form="form"
                                name="is_motor"
                                label="Saya memiliki dan/atau menguasai sepeda motor dan siap menggunakannya saat pendaataan"
                            />
                            <TanStackCheckbox
                                v-if="values.jenis_kelamin === 'Perempuan'"
                                :form="form"
                                name="is_not_hamil"
                                label="Saya sedang tidak dalam kondisi hamil"
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
                            <form.Field name="ktp_file">
                                <template #default="{ field }">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-1">
                                            <label class="text-sm font-medium">Upload KTP (Opsional)</label>
                                            <a
                                                :href="media + 'img/contoh/ktp.jpg'"
                                                target="_blank"
                                                class="text-xs text-cyan-600 underline-offset-2 hover:underline"
                                                >(contoh)</a
                                            >
                                        </div>
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
                                        <p class="text-xs text-slate-500">Format: pdf/jpg/jpeg/png. Maksimal 2MB.</p>
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
                        <div class="md:col-span-2">
                            <form.Field name="spek_hp_file">
                                <template #default="{ field }">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-1">
                                            <label class="text-sm font-medium">Upload Tangkapan Layar Spesifikasi HP (Opsional)</label>
                                            <a
                                                :href="media + 'img/contoh/spek_hp.jpg'"
                                                target="_blank"
                                                class="text-xs text-cyan-600 underline-offset-2 hover:underline"
                                                >(contoh)</a
                                            >
                                        </div>
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
                                        <p class="text-xs text-slate-500">Format: pdf/jpg/jpeg/png. Maksimal 2MB.</p>
                                        <p v-if="field.state.meta.errors.length" class="mt-1 text-xs text-destructive">
                                            {{ field.state.meta.errors.join(', ') }}
                                        </p>
                                        <p v-else-if="$page.props.errors?.spek_hp_file" class="mt-1 text-xs text-destructive">
                                            {{ $page.props.errors.spek_hp_file }}
                                        </p>
                                        <a
                                            v-if="temporarySpekHpPreviewUrl"
                                            :href="temporarySpekHpPreviewUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex text-xs font-medium text-cyan-700 underline underline-offset-2 hover:text-cyan-800"
                                        >
                                            Lihat file yang dipilih
                                        </a>
                                    </div>
                                </template>
                            </form.Field>
                        </div>
                        <div class="md:col-span-2">
                            <form.Field name="follow_ig_file">
                                <template #default="{ field }">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-1">
                                            <label class="text-sm font-medium">Upload Bukti Follow IG BPS KSB (Opsional)</label>
                                            <a
                                                :href="media + 'img/contoh/ss_ig.jpg'"
                                                target="_blank"
                                                class="text-xs text-cyan-600 underline-offset-2 hover:underline"
                                                >(contoh)</a
                                            >
                                        </div>
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
                                        <p class="text-xs text-slate-500">Format: pdf/jpg/jpeg/png. Maksimal 2MB.</p>
                                        <p v-if="field.state.meta.errors.length" class="mt-1 text-xs text-destructive">
                                            {{ field.state.meta.errors.join(', ') }}
                                        </p>
                                        <p v-else-if="$page.props.errors?.follow_ig_file" class="mt-1 text-xs text-destructive">
                                            {{ $page.props.errors.follow_ig_file }}
                                        </p>
                                        <a
                                            v-if="temporaryFollowIgPreviewUrl"
                                            :href="temporaryFollowIgPreviewUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex text-xs font-medium text-cyan-700 underline underline-offset-2 hover:text-cyan-800"
                                        >
                                            Lihat file yang dipilih
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

    <Dialog :open="isConfirmDialogOpen" @update:open="isConfirmDialogOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Konfirmasi Pendaftaran</DialogTitle>
                <DialogDescription>
                    Apakah Anda yakin ingin mengirim pendaftaran ini? Tolong pastikan lagi informasi yang diberikan telah sesuai, terutama nomor
                    WhatsApp harus benar dan aktif.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                <Button variant="outline" @click="isConfirmDialogOpen = false">Batal</Button>
                <Button class="bg-green-600 text-white hover:bg-green-700" @click="performSubmission">Ya, Kirim Pendaftaran</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
