<script setup lang="ts">
import { updateEmail } from '@/actions/App/Http/Controllers/ProfileController';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TanStackInput } from '@/components/ui/form';
import { router, usePage } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { watch } from 'vue';

const props = defineProps<{
    open: boolean;
    currentEmail: string | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const emailForm = useForm({
    defaultValues: {
        email_gmail: '',
    },
    onSubmit: async ({ value }) => {
        return new Promise<void>((resolve) => {
            router.put(updateEmail.url(), value, {
                onSuccess: () => {
                    emit('update:open', false);
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
            emailForm.reset();
            usePage().props.errors = {};
            emailForm.setFieldValue('email_gmail', props.currentEmail || '');
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="z-100 max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Ganti Gmail</DialogTitle>
                <DialogDescription> Perbarui alamat email Gmail Anda. </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="emailForm.handleSubmit" class="space-y-4 py-4">
                <TanStackInput
                    :form="emailForm"
                    name="email_gmail"
                    type="email"
                    label="Email Gmail"
                    placeholder="nama@gmail.com"
                    :validators="{
                        onChange: ({ value }: any) => {
                            if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Format email tidak valid.';
                            return undefined;
                        },
                    }"
                />

                <DialogFooter>
                    <emailForm.Subscribe :selector="(state) => state.isSubmitting">
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
                            <Button type="submit" class="cursor-pointer" :disabled="isSubmitting"> Simpan </Button>
                        </template>
                    </emailForm.Subscribe>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
