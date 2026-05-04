<script setup lang="ts">
import CreateRoleDialog from '@/components/role/CreateRoleDialog.vue';
import DeleteRoleDialog from '@/components/role/DeleteRoleDialog.vue';
import { getRoleColumns, type RoleItem } from '@/components/role/role-columns';
import UpdateRoleDialog from '@/components/role/UpdateRoleDialog.vue';
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Head } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    roles: RoleItem[];
}>();

defineOptions({
    layout: LayoutAdmin,
});

const isCreateModalOpen = ref(false);
const isUpdateModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedRole = ref<RoleItem | null>(null);

function handleEditRole(item: RoleItem): void {
    selectedRole.value = item;
    isUpdateModalOpen.value = true;
}

function handleDeleteRole(item: RoleItem): void {
    selectedRole.value = item;
    isDeleteModalOpen.value = true;
}

const tableColumns = getRoleColumns({
    onEdit: handleEditRole,
    onDelete: handleDeleteRole,
});
</script>

<template>
    <Head title="Kelola Role" />

    <div class="mx-auto max-w-5xl space-y-5">
        <header class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Admin</p>
            <h1 class="text-2xl font-bold text-slate-900">Kelola Role</h1>
            <p class="mt-1 text-sm text-slate-600">CRUD role akses pengguna internal BPS.</p>
        </header>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <DataTable
                :data="roles"
                :columns="tableColumns"
                search-placeholder="Cari nama role..."
                search-column="name"
            >
                <template #actions>
                    <Button class="h-8 cursor-pointer gap-1.5" size="sm" @click="isCreateModalOpen = true">
                        <Plus class="h-4 w-4" />
                        <span>Tambah Role</span>
                    </Button>
                </template>
            </DataTable>
        </section>
    </div>

    <CreateRoleDialog v-model:open="isCreateModalOpen" />
    <UpdateRoleDialog v-model:open="isUpdateModalOpen" :role="selectedRole" />
    <DeleteRoleDialog v-model:open="isDeleteModalOpen" :role="selectedRole" />
</template>
