<script setup lang="ts">
import { update } from '@/actions/Modules/Admin/Http/Controllers/RoleController';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TanStackCombobox, TanStackInput } from '@/components/ui/form';
import { router, usePage } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { watch } from 'vue';
import type { RoleItem } from './role-columns';

const props = defineProps<{
    open: boolean;
    role: RoleItem | null;
    availableUsers: { value: string; label: string }[];
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const updateRoleForm = useForm({
    defaultValues: {
        name: '',
        description: '',
        users: [] as string[],
    },
    onSubmit: async ({ value, formApi }) => {
        if (!props.role) return;
        return new Promise<void>((resolve) => {
            router.put(update.url(props.role!.id), value, {
                onSuccess: () => {
                    emit('update:open', false);
                    formApi.reset();
                },
                onFinish: () => resolve(),
            });
        });
    },
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            updateRoleForm.reset();
            usePage().props.errors = {};
            if (props.role) {
                updateRoleForm.setFieldValue('name', props.role.name || '');
                updateRoleForm.setFieldValue('description', props.role.description || '');
                updateRoleForm.setFieldValue(
                    'users',
                    props.role.users ? props.role.users.map((u) => String(u.id)) : []
                );
            }
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-[425px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Ubah Role</DialogTitle>
                <DialogDescription>Perbarui data role untuk sistem aplikasi.</DialogDescription>
            </DialogHeader>
            <form @submit.prevent="updateRoleForm.handleSubmit" class="space-y-4 py-4">
                <TanStackInput
                    :form="updateRoleForm"
                    name="name"
                    label="Nama Role*"
                    :validators="{
                        onChange: ({ value }: any) => (!value ? 'Nama Role wajib diisi' : undefined),
                    }"
                />

                <TanStackInput
                    :form="updateRoleForm"
                    name="description"
                    label="Deskripsi"
                />

                <TanStackCombobox
                    :form="updateRoleForm"
                    name="users"
                    label="Assign User ke Role"
                    :options="availableUsers"
                    multiple
                    placeholder="Pilih user yang mendapatkan role ini"
                />

                <DialogFooter>
                    <updateRoleForm.Subscribe :selector="(state) => state.isSubmitting">
                        <template #default="isSubmitting">
                            <Button
                                type="button"
                                class="cursor-pointer"
                                variant="outline"
                                @click="emit('update:open', false)"
                                :disabled="isSubmitting"
                            >
                                Batal
                            </Button>
                            <Button type="submit" class="cursor-pointer" :disabled="isSubmitting">Simpan Perubahan</Button>
                        </template>
                    </updateRoleForm.Subscribe>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
