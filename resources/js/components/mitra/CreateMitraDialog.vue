<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TanStackDatePicker, TanStackInput, TanStackSelect, TanStackTextarea } from '@/components/ui/form';
import { router } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';

defineProps<{
    open: boolean;
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
        nama_lengkap: '',
        email: '',
        jenis_kelamin: 'Laki-laki',
        kode_kec: '',
        kode_desa: '',
        alamat_lengkap: '',
        tanggal_lahir: '',
        tempat_lahir: '',
        status_perkawinan: 'Belum Kawin',
        pendidikan_terakhir: 'SLTA',
        pekerjaan: '',
        nomor_whatsapp: '',
        riwayat_kegiatan_bps: '',
        status_sobat: 'Belum',
        status_wawancara: 'Belum Wawancara',
        status_kelulusan: 'Belum Lulus',
    },
    onSubmit: async ({ value, formApi }) =>
        new Promise<void>((resolve) => {
            router.post('/admin/mitra', value, {
                onSuccess: () => {
                    emit('update:open', false);
                    formApi.reset();
                },
                onFinish: () => resolve(),
            });
        }),
});
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[760px]">
            <DialogHeader>
                <DialogTitle>Tambah Mitra</DialogTitle>
                <DialogDescription>Lengkapi data calon mitra untuk proses wawancara.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="form.handleSubmit" class="space-y-4 py-2">
                <div class="grid gap-4 md:grid-cols-2">
                    <TanStackInput :form="form" name="nama_lengkap" label="Nama Lengkap*" />
                    <TanStackInput :form="form" name="email" label="Email*" type="email" />
                    <TanStackSelect :form="form" name="jenis_kelamin" label="Jenis Kelamin*" :options="selectOptions.jenisKelamin" />
                    <TanStackInput :form="form" name="nomor_whatsapp" label="Nomor WhatsApp*" />
                    <TanStackInput :form="form" name="kode_kec" label="Kode Kecamatan*" />
                    <TanStackInput :form="form" name="kode_desa" label="Kode Desa*" />
                    <TanStackDatePicker :form="form" name="tanggal_lahir" label="Tanggal Lahir*" />
                    <TanStackInput :form="form" name="tempat_lahir" label="Tempat Lahir*" />
                    <TanStackSelect :form="form" name="status_perkawinan" label="Status Perkawinan*" :options="selectOptions.statusPerkawinan" />
                    <TanStackSelect :form="form" name="pendidikan_terakhir" label="Pendidikan Terakhir*" :options="selectOptions.pendidikanTerakhir" />
                    <TanStackInput :form="form" name="pekerjaan" label="Pekerjaan*" />
                    <TanStackSelect :form="form" name="status_sobat" label="Status Sobat*" :options="selectOptions.statusSobat" />
                    <TanStackSelect :form="form" name="status_wawancara" label="Status Wawancara*" :options="selectOptions.statusWawancara" />
                    <TanStackSelect :form="form" name="status_kelulusan" label="Status Kelulusan*" :options="selectOptions.statusKelulusan" />
                </div>

                <TanStackTextarea :form="form" name="alamat_lengkap" label="Alamat Lengkap*" :rows="3" />
                <TanStackTextarea :form="form" name="riwayat_kegiatan_bps" label="Riwayat Kegiatan BPS" :rows="3" />

                <DialogFooter>
                    <form.Subscribe :selector="(state) => state.isSubmitting">
                        <template #default="isSubmitting">
                            <Button type="button" variant="outline" class="cursor-pointer" :disabled="isSubmitting" @click="emit('update:open', false)">
                                Batal
                            </Button>
                            <Button type="submit" class="cursor-pointer" :disabled="isSubmitting">Simpan Mitra</Button>
                        </template>
                    </form.Subscribe>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
