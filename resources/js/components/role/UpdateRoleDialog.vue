<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TanStackInput } from '@/components/ui/form';
import { router } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { watch } from 'vue';
import type { RoleItem } from './role-columns';

const props = defineProps<{
    open: boolean;
    role: RoleItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const form = useForm({
    defaultValues: {
        name: '',
        description: '',
    },
    onSubmit: async ({ value, formApi }) => {
        if (!props.role) {
            return;
        }

        return new Promise<void>((resolve) => {
            router.put(`/admin/roles/${props.role?.id}`, value, {
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
        if (!isOpen || !props.role) {
            return;
        }

        form.setFieldValue('name', props.role.name);
        form.setFieldValue('description', props.role.description ?? '');
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[460px]">
            <DialogHeader>
                <DialogTitle>Ubah Role</DialogTitle>
                <DialogDescription>Perbarui data role yang dipilih.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="form.handleSubmit" class="space-y-4 py-2">
                <TanStackInput :form="form" name="name" label="Nama Role*" />
                <TanStackInput :form="form" name="description" label="Deskripsi" />

                <DialogFooter>
                    <form.Subscribe :selector="(state) => state.isSubmitting">
                        <template #default="isSubmitting">
                            <Button type="button" variant="outline" class="cursor-pointer" :disabled="isSubmitting" @click="emit('update:open', false)">
                                Batal
                            </Button>
                            <Button type="submit" class="cursor-pointer" :disabled="isSubmitting">Simpan Perubahan</Button>
                        </template>
                    </form.Subscribe>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
