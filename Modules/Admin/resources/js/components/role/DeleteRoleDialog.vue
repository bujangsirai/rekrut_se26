<script setup lang="ts">
import { destroy } from '@/actions/Modules/Admin/Http/Controllers/RoleController';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { RoleItem } from './role-columns';

const props = defineProps<{
    open: boolean;
    role: RoleItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isSubmitting = ref(false);

const handleDelete = () => {
    if (!props.role) return;
    
    isSubmitting.value = true;
    router.delete(destroy.url(props.role.id), {
        onSuccess: () => {
            emit('update:open', false);
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            usePage().props.errors = {};
            isSubmitting.value = false;
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !isSubmitting && emit('update:open', val)">
        <DialogContent class="sm:max-w-[425px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="text-destructive">Hapus Role?</DialogTitle>
                <DialogDescription v-if="role">
                    Apakah Anda yakin ingin menghapus role <span class="font-semibold text-foreground">{{ role.name }}</span>?
                    <br />
                    Tindakan ini tidak dapat dibatalkan.
                    
                    <div v-if="usePage().props.errors.error" class="mt-4 p-3 bg-destructive/10 border border-destructive/20 rounded-md text-sm text-destructive">
                        {{ usePage().props.errors.error }}
                    </div>
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="mt-4">
                <Button
                    type="button"
                    variant="outline"
                    class="cursor-pointer"
                    @click="emit('update:open', false)"
                    :disabled="isSubmitting"
                >
                    Batal
                </Button>
                <Button 
                    type="button" 
                    variant="destructive"
                    class="cursor-pointer"
                    @click="handleDelete" 
                    :disabled="isSubmitting"
                >
                    {{ isSubmitting ? 'Menghapus...' : 'Ya, Hapus Role' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
