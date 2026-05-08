<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TanStackCombobox, TanStackDatePicker, TanStackInput, TanStackSelect, TanStackTextarea } from '@/components/ui/form';
import { router } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { computed, watch } from 'vue';
import type { MitraItem } from './mitra-columns';

const props = defineProps<{
    open: boolean;
    mitra: MitraItem | null;
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

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const selectOptions = {
    jenisKelamin: [
        { label: 'Laki-laki', value: 'Laki-laki' },
        { label: 'Perempuan', value: 'Perempuan' },
    ],
    statusPerkawinan: [
        { label: 'Belum Kawin', value: 'Belum Kawin' },
        { label: 'Kawin', value: 'Kawin' },
        { label: 'Cerai Hidup', value: 'Cerai Hidup' },
        { label: 'Cerai Mati', value: 'Cerai Mati' },
    ],
    pendidikanTerakhir: [
        { label: 'SLTP/Kebawah', value: 'SLTP/Kebawah' },
        { label: 'SLTA', value: 'SLTA' },
        { label: 'DI/DII/DII', value: 'DI/DII/DII' },
        { label: 'DIV/S1/S2/S3', value: 'DIV/S1/S2/S3' },
    ],
    statusSobat: [
        { label: 'Sudah', value: 'Sudah' },
        { label: 'Belum', value: 'Belum' },
    ],
    statusWawancara: [
        { label: 'Belum Wawancara', value: 'Belum Wawancara' },
        { label: 'Sudah Wawancara', value: 'Sudah Wawancara' },
    ],
    statusKelulusan: [
        { label: 'Lulus', value: 'Lulus' },
        { label: 'Belum Lulus', value: 'Belum Lulus' },
    ],
};

const form = useForm({
    defaultValues: {
        nik: '',
        nama_lengkap: '',
        email: '',
        jenis_kelamin: 'Laki-laki',
        kode_kec: '',
        kode_desa: '',
        alamat_lengkap: '',
        is_domksb: false,
        kode_kec_dom: '',
        kode_desa_dom: '',
        tanggal_lahir: '',
        tempat_lahir: '',
        status_perkawinan: 'Belum Kawin',
        pendidikan_terakhir: 'SLTA',
        pekerjaan: '',
        is_not_asn: false,
        is_not_hamil: true,
        is_motor: false,
        nomor_whatsapp: '',
        merk_hp: '',
        riwayat_kegiatan_bps: '',
        status_sobat: 'Belum',
        status_wawancara: 'Belum Wawancara',
        status_kelulusan: 'Belum Lulus',
    },
    onSubmit: async ({ value, formApi }) => {
        if (!props.mitra) {
            return;
        }

        return new Promise<void>((resolve) => {
            router.put(`/admin/mitra/${props.mitra?.id}`, value, {
                onSuccess: () => {
                    emit('update:open', false);
                    formApi.reset();
                },
                onFinish: () => resolve(),
            });
        });
    },
});

const kecamatanSelectOptions = computed(() =>
    props.kecamatanOptions.map((item) => ({
        label: item.nama_kec,
        value: item.kode_kec,
    })),
);

const values = form.useStore((state) => state.values);

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
    () => values.value.kode_kec,
    (kodeKec, oldVal) => {
        if (!kodeKec || !oldVal) {
            return;
        }

        const firstDesa = props.desaOptions.find((item) => item.kode_desa.slice(0, 7) === kodeKec)?.kode_desa ?? '';
        form.setFieldValue('kode_desa', firstDesa);
    },
);

watch(
    () => values.value.kode_kec_dom,
    (kodeKecDom, oldVal) => {
        if (!kodeKecDom || !oldVal) {
            return;
        }

        const firstDesa = props.desaOptions.find((item) => item.kode_desa.slice(0, 7) === kodeKecDom)?.kode_desa ?? '';
        form.setFieldValue('kode_desa_dom', firstDesa);
    },
);

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen || !props.mitra) {
            return;
        }

        form.setFieldValue('nik', props.mitra.nik);
        form.setFieldValue('nama_lengkap', props.mitra.nama_lengkap);
        form.setFieldValue('email', props.mitra.email);
        form.setFieldValue('jenis_kelamin', props.mitra.jenis_kelamin);
        form.setFieldValue('kode_kec', props.mitra.kode_kec);
        form.setFieldValue('kode_desa', props.mitra.kode_desa);
        form.setFieldValue('alamat_lengkap', props.mitra.alamat_lengkap);
        form.setFieldValue('is_domksb', props.mitra.is_domksb);
        form.setFieldValue('kode_kec_dom', props.mitra.kode_kec_dom ?? '');
        form.setFieldValue('kode_desa_dom', props.mitra.kode_desa_dom ?? '');
        form.setFieldValue('tanggal_lahir', props.mitra.tanggal_lahir);
        form.setFieldValue('tempat_lahir', props.mitra.tempat_lahir);
        form.setFieldValue('status_perkawinan', props.mitra.status_perkawinan);
        form.setFieldValue('pendidikan_terakhir', props.mitra.pendidikan_terakhir);
        form.setFieldValue('pekerjaan', props.mitra.pekerjaan);
        form.setFieldValue('is_not_asn', props.mitra.is_not_asn);
        form.setFieldValue('is_not_hamil', props.mitra.is_not_hamil);
        form.setFieldValue('is_motor', props.mitra.is_motor);
        form.setFieldValue('nomor_whatsapp', props.mitra.nomor_whatsapp);
        form.setFieldValue('merk_hp', props.mitra.merk_hp);
        form.setFieldValue('riwayat_kegiatan_bps', props.mitra.riwayat_kegiatan_bps ?? '');
        form.setFieldValue('status_sobat', props.mitra.status_sobat);
        form.setFieldValue('status_wawancara', props.mitra.status_wawancara);
        form.setFieldValue('status_kelulusan', props.mitra.status_kelulusan);
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[800px]">
            <DialogHeader>
                <DialogTitle>Ubah Mitra</DialogTitle>
                <DialogDescription>Perbarui data mitra sesuai kebutuhan wawancara.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="form.handleSubmit" class="space-y-4 py-2">
                <div class="grid gap-4 md:grid-cols-2">
                    <TanStackInput :form="form" name="nik" label="NIK (16 Digit)*" placeholder="Contoh: 5207xxxxxxxxxxxx" :number-only="true" :maxlength="16" />
                    <TanStackInput :form="form" name="nama_lengkap" label="Nama Lengkap*" placeholder="Masukkan nama lengkap sesuai KTP" />

                    <TanStackCombobox :form="form" name="kode_kec" label="Asal Kecamatan (KTP)*" :options="kecamatanSelectOptions" placeholder="Cari kecamatan..." />
                    <TanStackCombobox :form="form" name="kode_desa" label="Asal Desa (KTP)*" :options="desaSelectOptions" placeholder="Cari desa..." />
                    
                    <div class="md:col-span-2">
                        <TanStackCheckbox :form="form" name="is_domksb" label="Domisili saat ini di Sumbawa Barat" />
                    </div>

                    <template v-if="values.is_domksb">
                        <TanStackCombobox :form="form" name="kode_kec_dom" label="Kecamatan Domisili*" :options="kecamatanSelectOptions" placeholder="Cari kecamatan..." />
                        <TanStackCombobox :form="form" name="kode_desa_dom" label="Desa Domisili*" :options="desaDomSelectOptions" placeholder="Cari desa..." />
                    </template>

                    <TanStackInput :form="form" name="tempat_lahir" label="Tempat Lahir*" placeholder="Contoh: Sumbawa Barat" />
                    <TanStackDatePicker :form="form" name="tanggal_lahir" label="Tanggal Lahir*" />
                    
                    <TanStackSelect :form="form" name="jenis_kelamin" label="Jenis Kelamin*" :options="selectOptions.jenisKelamin" />
                    <TanStackSelect :form="form" name="status_perkawinan" label="Status Perkawinan*" :options="selectOptions.statusPerkawinan" />
                    <TanStackSelect :form="form" name="pendidikan_terakhir" label="Pendidikan Terakhir*" :options="selectOptions.pendidikanTerakhir" />
                    
                    <TanStackInput :form="form" name="pekerjaan" label="Pekerjaan*" placeholder="Contoh: Wiraswasta / Pegawai Swasta" />
                    <TanStackInput :form="form" name="nomor_whatsapp" label="Nomor WhatsApp*" placeholder="Contoh: 081234567890" />
                    <TanStackInput :form="form" name="email" type="email" label="Alamat Email*" placeholder="Contoh: nama@email.com" />
                    <TanStackInput :form="form" name="merk_hp" label="Merek Handphone*" placeholder="Contoh: Samsung Galaxy A54" />
                    
                    <div class="md:col-span-2">
                        <TanStackInput :form="form" name="alamat_lengkap" label="Alamat Lengkap*" placeholder="Masukkan alamat domisili saat ini" />
                    </div>

                    <div class="md:col-span-2">
                        <TanStackTextarea :form="form" name="riwayat_kegiatan_bps" label="Riwayat Kegiatan BPS" placeholder="Sebutkan kegiatan BPS yang pernah diikuti, dipisahkan dengan tanda koma" :rows="3" />
                    </div>

                    <div class="flex flex-col gap-2 md:col-span-2">
                        <TanStackCheckbox :form="form" name="is_not_asn" label="Bukan Aparatur Sipil Negara/TNI/Polri" />
                        <TanStackCheckbox v-if="values.jenis_kelamin === 'Perempuan'" :form="form" name="is_not_hamil" label="Sedang tidak dalam kondisi hamil" />
                        <TanStackCheckbox :form="form" name="is_motor" label="Memiliki dan/atau menguasai sepeda motor" />
                    </div>

                    <div class="col-span-full mt-2 mb-1 border-b border-slate-200"></div>
                    <div class="col-span-full">
                        <h3 class="text-sm font-semibold text-slate-800">Status Seleksi</h3>
                    </div>

                    <TanStackSelect :form="form" name="status_sobat" label="Status Sobat*" :options="selectOptions.statusSobat" />
                    <TanStackSelect :form="form" name="status_wawancara" label="Status Wawancara*" :options="selectOptions.statusWawancara" />
                    <TanStackSelect :form="form" name="status_kelulusan" label="Status Kelulusan*" :options="selectOptions.statusKelulusan" />
                </div>

                <DialogFooter>
                    <form.Subscribe :selector="(state) => state.isSubmitting">
                        <template #default="isSubmitting">
                            <Button type="button" variant="outline" class="cursor-pointer" :disabled="isSubmitting" @click="emit('update:open', false)">
                                Batal
                            </Button>
                            <Button type="submit" class="cursor-pointer" :disabled="isSubmitting">Simpan Perubahan</Button>
                        </template>
                    </form.Subscribe>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
