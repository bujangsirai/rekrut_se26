<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { MitraItem } from './mitra-columns';

const props = defineProps<{
    open: boolean;
    mitra: MitraItem | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

const isSubmitting = ref(false);

function handleDelete(): void {
    if (!props.mitra) {
        return;
    }

    isSubmitting.value = true;
    router.delete(`/admin/mitra/${props.mitra.id}`, {
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
                <DialogTitle class="text-destructive">Hapus Mitra?</DialogTitle>
                <DialogDescription v-if="mitra">
                    Data <span class="font-semibold text-foreground">{{ mitra.nama_lengkap }}</span> akan dihapus permanen.
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <Button type="button" variant="outline" class="cursor-pointer" :disabled="isSubmitting" @click="emit('update:open', false)">
                    Batal
                </Button>
                <Button type="button" variant="destructive" class="cursor-pointer" :disabled="isSubmitting" @click="handleDelete">
                    {{ isSubmitting ? 'Menghapus...' : 'Ya, Hapus Mitra' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
