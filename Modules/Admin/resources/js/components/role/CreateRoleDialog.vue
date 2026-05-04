<script setup lang="ts">
import { store } from '@/actions/Modules/Admin/Http/Controllers/RoleController';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TanStackInput } from '@/components/ui/form';
import { router, usePage } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { watch } from 'vue';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const createRoleForm = useForm({
    defaultValues: {
        name: '',
        description: '',
    },
    onSubmit: async ({ value, formApi }) => {
        return new Promise<void>((resolve) => {
            router.post(store.url(), value, {
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
            createRoleForm.reset();
            usePage().props.errors = {};
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Tambah Role</DialogTitle>
                <DialogDescription>Buat role baru untuk sistem aplikasi.</DialogDescription>
            </DialogHeader>
            <form @submit.prevent="createRoleForm.handleSubmit" class="space-y-4 py-4">
                <TanStackInput
                    :form="createRoleForm"
                    name="name"
                    label="Nama Role*"
                    :validators="{
                        onChange: ({ value }: any) => (!value ? 'Nama Role wajib diisi' : undefined),
                    }"
                />

                <TanStackInput :form="createRoleForm" name="description" label="Deskripsi" />

                <DialogFooter>
                    <createRoleForm.Subscribe :selector="(state) => state.isSubmitting">
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
                            <Button type="submit" class="cursor-pointer" :disabled="isSubmitting">Simpan Role</Button>
                        </template>
                    </createRoleForm.Subscribe>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
