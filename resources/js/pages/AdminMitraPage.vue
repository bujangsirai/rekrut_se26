<script setup lang="ts">
import DeleteMitraDialog from '@/components/mitra/DeleteMitraDialog.vue';
import { getMitraColumns, type MitraItem } from '@/components/mitra/mitra-columns';
import MitraExpandedRow from '@/components/mitra/MitraExpandedRow.vue';
import UpdateMitraDialog from '@/components/mitra/UpdateMitraDialog.vue';
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Head } from '@inertiajs/vue3';
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
    <Head title="Kelola Mitra" />

    <div class="mx-auto max-w-6xl space-y-5">
        <header class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">Kelola Mitra</h1>
            <p class="mt-1 text-sm text-slate-600">CRUD data mitra calon petugas untuk proses wawancara dan kelulusan.</p>
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
