<script setup lang="ts">
import { updateProfile } from '@/actions/Modules/Admin/Http/Controllers/UserController';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TanStackCombobox, TanStackInput } from '@/components/ui/form';
import { router, usePage } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { watch } from 'vue';

const props = defineProps<{
    open: boolean;
    userId: number | null;
    userName: string;
    currentUserRoles: string[];
    availableRoles: string[];
    emailGmail: string | null;
    statusPegawai: string | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const statusOptions = ['Aktif', 'Tidak Aktif'];

const profileForm = useForm({
    defaultValues: {
        email_gmail: '',
        status_pegawai: '',
        roles: [] as string[],
    },
    onSubmit: async ({ value, formApi }) => {
        const userId = props.userId;
        if (!userId) return;
        return new Promise<void>((resolve) => {
            router.put(updateProfile.url(userId), value, {
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
            profileForm.reset();
            usePage().props.errors = {};

            let prefilledStatus = props.statusPegawai || '';
            if (prefilledStatus.toLowerCase() === 'aktif') prefilledStatus = 'Aktif';
            else if (prefilledStatus.toLowerCase() === 'tidak aktif') prefilledStatus = 'Tidak Aktif';

            profileForm.setFieldValue('email_gmail', props.emailGmail || '');
            profileForm.setFieldValue('status_pegawai', prefilledStatus);
            profileForm.setFieldValue('roles', props.currentUserRoles.length ? [...props.currentUserRoles] : []);
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Ubah Profil</DialogTitle>
                <DialogDescription> Perbarui profil dan role untuk {{ userName }}. </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="profileForm.handleSubmit" class="space-y-4 py-4">
                <TanStackInput
                    :form="profileForm"
                    name="email_gmail"
                    type="email"
                    label="Gmail"
                    :validators="{
                        onChange: ({ value }: any) => {
                            if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Format email tidak valid.';
                            return undefined;
                        },
                    }"
                />

                <TanStackCombobox
                    :form="profileForm"
                    name="status_pegawai"
                    label="Status Pegawai*"
                    :options="statusOptions"
                    :validators="{
                        onChange: ({ value }: any) => (!value ? 'Status Pegawai harus dipilih' : undefined),
                    }"
                />

                <TanStackCombobox
                    :form="profileForm"
                    name="roles"
                    label="Pilih Role*"
                    :options="availableRoles"
                    multiple
                    :validators="{
                        onChange: ({ value }: any) => (!value || value.length === 0 ? 'Minimal harus memilih 1 role' : undefined),
                    }"
                />

                <DialogFooter>
                    <profileForm.Subscribe :selector="(state) => state.isSubmitting">
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
                            <Button type="submit" class="cursor-pointer" :disabled="isSubmitting"> Simpan Perubahan </Button>
                        </template>
                    </profileForm.Subscribe>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
