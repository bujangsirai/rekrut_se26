<script setup lang="ts">
import DeleteMitraDialog from '@/components/mitra/DeleteMitraDialog.vue';
import { getMitraColumns, type MitraItem } from '@/components/mitra/mitra-seleksi-columns';
import MitraExpandedRow from '@/components/mitra/MitraExpandedRow.vue';
import UpdateMitraDialog from '@/components/mitra/UpdateMitraDialog.vue';
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Head, router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import mitraRoute from '@/routes/admin/mitra';

const props = defineProps<{
    mitra: MitraItem[];
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

defineOptions({
    layout: LayoutAdmin,
});

const isUpdateModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedMitra = ref<MitraItem | null>(null);
const sobatUpdatingIds = ref<number[]>([]);
const mitraKepkaUpdatingIds = ref<number[]>([]);

function buildUpdatePayload(item: MitraItem, statusSobat: MitraItem['status_sobat']) {
    return {
        nik: item.nik,
        nama_lengkap: item.nama_lengkap,
        email: item.email,
        jenis_kelamin: item.jenis_kelamin,
        kode_kec: item.kode_kec,
        kode_desa: item.kode_desa,
        alamat_lengkap: item.alamat_lengkap,
        tanggal_lahir: item.tanggal_lahir,
        tempat_lahir: item.tempat_lahir,
        status_perkawinan: item.status_perkawinan,
        pendidikan_terakhir: item.pendidikan_terakhir,
        pekerjaan: item.pekerjaan,
        nomor_whatsapp: item.nomor_whatsapp,
        riwayat_kegiatan_bps: item.riwayat_kegiatan_bps,
        is_domksb: item.is_domksb,
        is_motor: item.is_motor,
        is_not_asn: item.is_not_asn,
        is_not_hamil: item.is_not_hamil,
        merk_hp: item.merk_hp,
        kode_kec_dom: item.kode_kec_dom,
        kode_desa_dom: item.kode_desa_dom,
        status_sobat: statusSobat,
        is_mitrakepka: item.is_mitrakepka,
        status_wawancara: item.status_wawancara,
        status_kelulusan: item.status_kelulusan,
    };
}

function buildMitraKepkaUpdatePayload(item: MitraItem, isMitraKepka: boolean) {
    return {
        ...buildUpdatePayload(item, item.status_sobat),
        is_mitrakepka: isMitraKepka,
    };
}

function isSobatUpdating(mitraId: number): boolean {
    return sobatUpdatingIds.value.includes(mitraId);
}

function isMitraKepkaUpdating(mitraId: number): boolean {
    return mitraKepkaUpdatingIds.value.includes(mitraId);
}

function handleChangeSobat(item: MitraItem, statusSobat: MitraItem['status_sobat']): void {
    if (item.status_sobat === statusSobat || isSobatUpdating(item.id)) {
        return;
    }

    sobatUpdatingIds.value.push(item.id);

    router.put(mitraRoute.update(item.id).url, buildUpdatePayload(item, statusSobat), {
        preserveScroll: true,
        preserveState: true,
        only: ['mitra'],
        onFinish: () => {
            sobatUpdatingIds.value = sobatUpdatingIds.value.filter((id) => id !== item.id);
        },
    });
}

function handleChangeMitraKepka(item: MitraItem, isMitraKepka: boolean): void {
    if (item.is_mitrakepka === isMitraKepka || isMitraKepkaUpdating(item.id)) {
        return;
    }

    mitraKepkaUpdatingIds.value.push(item.id);

    router.put(mitraRoute.update(item.id).url, buildMitraKepkaUpdatePayload(item, isMitraKepka), {
        preserveScroll: true,
        preserveState: true,
        only: ['mitra'],
        onFinish: () => {
            mitraKepkaUpdatingIds.value = mitraKepkaUpdatingIds.value.filter((id) => id !== item.id);
        },
    });
}

function handleEditMitra(item: MitraItem): void {
    selectedMitra.value = item;
    isUpdateModalOpen.value = true;
}

function handleDeleteMitra(item: MitraItem): void {
    selectedMitra.value = item;
    isDeleteModalOpen.value = true;
}

const tableColumns = getMitraColumns({
    onEdit: handleEditMitra,
    onDelete: handleDeleteMitra,
    onChangeSobat: handleChangeSobat,
    isSobatUpdating,
    onChangeMitraKepka: handleChangeMitraKepka,
    isMitraKepkaUpdating,
});

const domisiliKecamatanFilters = computed(() => [
    {
        key: 'kode_kec_dom',
        label: 'Kecamatan Domisili',
        placeholder: 'Semua Domisili',
        options: props.kecamatanOptions.map((item) => ({
            value: item.kode_kec,
            label: `${item.kode_kec} - ${item.nama_kec}`,
        })),
    },
]);
</script>

<template>
    <Head title="Seleksi Mitra" />

    <div class="mx-auto max-w-6xl space-y-5">
        <header class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">Seleksi Mitra</h1>
            <p class="mt-1 text-sm text-slate-600">Kumpulan data mitra dengan status sobat Sudah.</p>
        </header>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <DataTable
                :data="mitra"
                :columns="tableColumns"
                search-placeholder="Cari nama atau NIK..."
                :search-fields="['nama_lengkap', 'nik']"
                :toolbar-filters="domisiliKecamatanFilters"
                show-row-aggregate
                single-expanded-row
            >
                <template #actions>
                    <a :href="mitraRoute.export.url()" target="_blank">
                        <Button class="cursor-pointer">
                            <Download class="mr-2 h-4 w-4" />
                            Download Excel
                        </Button>
                    </a>
                </template>
                <template #expanded-row="{ original }">
                    <MitraExpandedRow :original="original" />
                </template>
            </DataTable>
        </section>
    </div>

    <UpdateMitraDialog
        v-model:open="isUpdateModalOpen"
        :mitra="selectedMitra"
        :kecamatan-options="kecamatanOptions"
        :desa-options="desaOptions"
    />
    <DeleteMitraDialog v-model:open="isDeleteModalOpen" :mitra="selectedMitra" />
</template>
