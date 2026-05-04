<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TanStackInput, TanStackSelect } from '@/components/ui/form';
import { router } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';

defineProps<{
    open: boolean;
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
    onSubmit: async ({ value, formApi }) =>
        new Promise<void>((resolve) => {
            router.post('/admin/users', value, {
                onSuccess: () => {
                    emit('update:open', false);
                    formApi.reset();
                },
                onFinish: () => resolve(),
            });
        }),
});
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[460px]">
            <DialogHeader>
                <DialogTitle>Tambah User</DialogTitle>
                <DialogDescription>Buat akun user organik baru.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="form.handleSubmit" class="space-y-4 py-2">
                <TanStackInput :form="form" name="username" label="Username*" />
                <TanStackInput :form="form" name="password" type="password" label="Password*" />
                <TanStackSelect :form="form" name="role" label="Role*" :options="availableRoles" placeholder="Pilih role" />

                <DialogFooter>
                    <form.Subscribe :selector="(state) => state.isSubmitting">
                        <template #default="isSubmitting">
                            <Button type="button" variant="outline" class="cursor-pointer" :disabled="isSubmitting" @click="emit('update:open', false)">
                                Batal
                            </Button>
                            <Button type="submit" class="cursor-pointer" :disabled="isSubmitting">Simpan User</Button>
                        </template>
                    </form.Subscribe>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
