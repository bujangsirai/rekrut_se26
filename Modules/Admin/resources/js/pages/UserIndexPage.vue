<script setup lang="ts">
import { DataTable } from '@/components/ui/data-table';
import { computed, ref } from 'vue';
import ModuleContentShell from '../../../../Shared/resources/js/components/modules/ModuleContentShell.vue';
import { getModulePageBreadcrumbs, type ModuleNavigationConfig } from '../../../../Shared/resources/js/lib/module-navigation';
import UpdatePasswordDialog from '../components/user/UpdatePasswordDialog.vue';
import UpdateProfileDialog from '../components/user/UpdateProfileDialog.vue';
import { getUserColumns, type UserItem } from '../components/user/user-columns';
import UserExpandedRow from '../components/user/UserExpandedRow.vue';
import moduleNavigation from '../config/module-navigation.json';

const props = defineProps<{
    users: UserItem[];
    roles: string[];
}>();

const breadcrumbs = getModulePageBreadcrumbs(moduleNavigation as ModuleNavigationConfig, 'users');

const passwordModalOpen = ref(false);
const profileModalOpen = ref(false);
const selectedUserId = ref<number | null>(null);

const selectedUser = computed(() => {
    if (!selectedUserId.value) return null;
    return props.users.find((u) => u.id === selectedUserId.value) || null;
});

const openProfileModal = (id: number) => {
    selectedUserId.value = id;
    profileModalOpen.value = true;
};

const openPasswordModal = (id: number) => {
    selectedUserId.value = id;
    passwordModalOpen.value = true;
};

const columns = computed(() =>
    getUserColumns({
        onEditPassword: openPasswordModal,
        onEditProfile: openProfileModal,
    }),
);
</script>

<template>
    <ModuleContentShell :breadcrumbs="breadcrumbs">
        <div class="space-y-4">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-foreground">Daftar Pengguna</h2>
            </div>
            <DataTable
                :data="users"
                :columns="columns"
                :search-fields="['nama', 'nip', 'nip_baru', 'jabatan', 'username', 'status_pegawai']"
                search-placeholder="Cari nama, NIP, jabatan..."
            >
                <template #expanded-row="{ original }">
                    <UserExpandedRow :original="original" />
                </template>
            </DataTable>
        </div>

        <UpdateProfileDialog
            v-model:open="profileModalOpen"
            :user-id="selectedUserId"
            :user-name="selectedUser?.nama || 'Pengguna'"
            :current-user-roles="selectedUser?.roles || []"
            :available-roles="roles"
            :email-gmail="selectedUser?.email_gmail || null"
            :status-pegawai="selectedUser?.status_pegawai || null"
        />
        <UpdatePasswordDialog v-model:open="passwordModalOpen" :user-id="selectedUserId" :user-name="selectedUser?.nama || 'Pengguna'" />
    </ModuleContentShell>
</template>
