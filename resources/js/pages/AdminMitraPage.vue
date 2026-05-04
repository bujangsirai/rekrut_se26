<script setup lang="ts">
import CreateMitraDialog from '@/components/mitra/CreateMitraDialog.vue';
import DeleteMitraDialog from '@/components/mitra/DeleteMitraDialog.vue';
import { getMitraColumns, type MitraItem } from '@/components/mitra/mitra-columns';
import MitraExpandedRow from '@/components/mitra/MitraExpandedRow.vue';
import UpdateMitraDialog from '@/components/mitra/UpdateMitraDialog.vue';
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Head } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    mitra: MitraItem[];
}>();

defineOptions({
    layout: LayoutAdmin,
});

const isCreateModalOpen = ref(false);
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
            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Wawancara</p>
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
                <template #actions>
                    <Button class="h-8 cursor-pointer gap-1.5" size="sm" @click="isCreateModalOpen = true">
                        <Plus class="h-4 w-4" />
                        <span>Tambah Mitra</span>
                    </Button>
                </template>

                <template #expanded-row="{ original }">
                    <MitraExpandedRow :original="original" />
                </template>
            </DataTable>
        </section>
    </div>

    <CreateMitraDialog v-model:open="isCreateModalOpen" />
    <UpdateMitraDialog v-model:open="isUpdateModalOpen" :mitra="selectedMitra" />
    <DeleteMitraDialog v-model:open="isDeleteModalOpen" :mitra="selectedMitra" />
</template>
