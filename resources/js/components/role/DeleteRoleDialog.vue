<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { RoleItem } from './role-columns';

const props = defineProps<{
    open: boolean;
    role: RoleItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isSubmitting = ref(false);
const serverError = computed(() => (usePage().props.errors as { error?: string })?.error);

function handleDelete(): void {
    if (!props.role) {
        return;
    }

    isSubmitting.value = true;
    router.delete(`/admin/roles/${props.role.id}`, {
        onSuccess: () => {
            emit('update:open', false);
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !isSubmitting && emit('update:open', val)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[460px]">
            <DialogHeader>
                <DialogTitle class="text-destructive">Hapus Role?</DialogTitle>
                <DialogDescription v-if="role">
                    Role <span class="font-semibold text-foreground">{{ role.name }}</span> akan dihapus permanen.
                    <span v-if="serverError" class="mt-2 block text-sm text-destructive">{{ serverError }}</span>
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <Button type="button" variant="outline" class="cursor-pointer" :disabled="isSubmitting" @click="emit('update:open', false)">
                    Batal
                </Button>
                <Button type="button" variant="destructive" class="cursor-pointer" :disabled="isSubmitting" @click="handleDelete">
                    {{ isSubmitting ? 'Menghapus...' : 'Ya, Hapus Role' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
