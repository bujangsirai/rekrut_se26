<script setup lang="ts">
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import CreateUserDialog from '@/components/user/CreateUserDialog.vue';
import DeleteUserDialog from '@/components/user/DeleteUserDialog.vue';
import { getUserColumns, type UserItem } from '@/components/user/user-columns';
import UpdateUserDialog from '@/components/user/UpdateUserDialog.vue';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/ui/data-table';
import { Head } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    users: UserItem[];
    availableRoles: { label: string; value: string }[];
}>();

defineOptions({
    layout: LayoutAdmin,
});

const isCreateModalOpen = ref(false);
const isUpdateModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedUser = ref<UserItem | null>(null);

function handleEditUser(item: UserItem): void {
    selectedUser.value = item;
    isUpdateModalOpen.value = true;
}

function handleDeleteUser(item: UserItem): void {
    selectedUser.value = item;
    isDeleteModalOpen.value = true;
}

const tableColumns = getUserColumns({
    onEdit: handleEditUser,
    onDelete: handleDeleteUser,
});
</script>

<template>
    <Head title="Kelola User" />

    <div class="mx-auto max-w-5xl space-y-5">
        <header class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Admin</p>
            <h1 class="text-2xl font-bold text-slate-900">Kelola User</h1>
            <p class="mt-1 text-sm text-slate-600">CRUD user login internal BPS (username & password).</p>
        </header>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <DataTable
                :data="users"
                :columns="tableColumns"
                search-placeholder="Cari nama user..."
                search-column="nama"
            >
                <template #actions>
                    <Button class="h-8 cursor-pointer gap-1.5" size="sm" @click="isCreateModalOpen = true">
                        <Plus class="h-4 w-4" />
                        <span>Tambah User</span>
                    </Button>
                </template>
            </DataTable>
        </section>
    </div>

    <CreateUserDialog v-model:open="isCreateModalOpen" :available-roles="availableRoles" />
    <UpdateUserDialog v-model:open="isUpdateModalOpen" :user="selectedUser" :available-roles="availableRoles" />
    <DeleteUserDialog v-model:open="isDeleteModalOpen" :user="selectedUser" />
</template>
