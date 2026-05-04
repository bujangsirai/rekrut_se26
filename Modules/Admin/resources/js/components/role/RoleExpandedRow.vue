<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { RoleItem } from './role-columns';

defineProps<{
    original: RoleItem;
}>();
</script>

<template>
    <div class="space-y-3 py-2 text-sm">
        <p class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
            Pengguna ({{ original.users.length }})
        </p>

        <div v-if="original.users.length > 0" class="flex flex-wrap gap-1.5">
            <Badge
                v-for="user in original.users"
                :key="user.id"
                class="font-normal text-xs"
            >
                {{ user.nama }}
            </Badge>
        </div>
        
        <div v-else class="rounded-lg border border-border border-dashed p-4 text-center">
            <p class="text-xs text-muted-foreground">Belum ada pengguna di role ini.</p>
        </div>
    </div>
</template>
