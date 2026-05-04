<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TanStackInput, TanStackSelect } from '@/components/ui/form';
import { router } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { watch } from 'vue';
import type { UserItem } from './user-columns';

const props = defineProps<{
    open: boolean;
    user: UserItem | null;
    availableRoles: { label: string; value: string }[];
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const form = useForm({
    defaultValues: {
        username: '',
        password: '',
        role: '',
    },
    onSubmit: async ({ value, formApi }) => {
        if (!props.user) {
            return;
        }

        return new Promise<void>((resolve) => {
            router.put(`/admin/users/${props.user?.id}`, value, {
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
        if (!isOpen || !props.user) {
            return;
        }

        form.setFieldValue('username', props.user.username);
        form.setFieldValue('password', '');
        form.setFieldValue('role', props.user.role ?? '');
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[460px]">
            <DialogHeader>
                <DialogTitle>Ubah User</DialogTitle>
                <DialogDescription>
                    Perbarui username atau isi password baru jika ingin mengganti password.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="form.handleSubmit" class="space-y-4 py-2">
                <TanStackInput :form="form" name="username" label="Username*" />
                <TanStackInput :form="form" name="password" type="password" label="Password Baru (opsional)" />
                <TanStackSelect :form="form" name="role" label="Role*" :options="availableRoles" placeholder="Pilih role" />

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
