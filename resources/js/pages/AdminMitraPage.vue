<script setup lang="ts">
import DeleteMitraDialog from '@/components/mitra/DeleteMitraDialog.vue';
import { getMitraColumns, type MitraItem } from '@/components/mitra/mitra-columns';
import MitraExpandedRow from '@/components/mitra/MitraExpandedRow.vue';
import UpdateMitraDialog from '@/components/mitra/UpdateMitraDialog.vue';
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
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
</script>

<template>
    <Head title="Kelola Mitra" />

    <div class="mx-auto max-w-6xl space-y-5">
        <header class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase"></p>
            <h1 class="text-2xl font-bold text-slate-900">Kelola Mitra</h1>
            <p class="mt-1 text-sm text-slate-600">CRUD data mitra calon petugas untuk proses wawancara dan kelulusan.</p>
        </header>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <DataTable
                :data="mitra"
                :columns="tableColumns"
                search-placeholder="Cari nama mitra..."
                search-column="nama_lengkap"
            >
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
