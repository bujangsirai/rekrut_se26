<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { UserItem } from './user-columns';

defineProps<{
    original: UserItem;
}>();

function capitalize(str: string): string {
    return str
        .split(' ')
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase())
        .join(' ');
}

function isAktif(status: string | null): boolean {
    if (!status) return false;
    const lower = status.toLowerCase();
    return lower.includes('aktif') && !lower.includes('tidak') && !lower.includes('non');
}
</script>

<template>
    <div class="grid grid-cols-2 gap-x-6 gap-y-4 py-1 text-sm">
        <!-- Username (Hanya Mobile) -->
        <div class="md:hidden">
            <p class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">Username</p>
            <p class="mt-0.5 truncate text-xs font-semibold text-foreground">{{ original.username }}</p>
        </div>

        <!-- Email Gmail (Hanya Mobile) -->
        <div class="md:hidden">
            <p class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">Gmail</p>
            <p class="mt-0.5 truncate text-xs text-foreground">{{ original.email_gmail ?? '-' }}</p>
        </div>

        <!-- Status (Hanya Mobile) -->
        <div class="md:hidden">
            <p class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">Status</p>
            <div class="mt-1">
                <Badge
                    v-if="original.status_pegawai"
                    variant="outline"
                    :class="
                        isAktif(original.status_pegawai)
                            ? 'border-emerald-500/30 bg-emerald-500/10 text-[10px] text-emerald-700 dark:text-emerald-400'
                            : 'border-destructive/30 bg-destructive/10 text-[10px] text-destructive'
                    "
                >
                    {{ capitalize(original.status_pegawai) }}
                </Badge>
                <span v-else class="text-xs text-muted-foreground">-</span>
            </div>
        </div>

        <!-- Role -->
        <div>
            <p class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">Role</p>
            <div class="mt-1 flex flex-wrap gap-1">
                <Badge v-for="role in original.roles" :key="role" variant="default" class="text-[10px]">
                    {{ role }}
                </Badge>
                <span v-if="original.roles.length === 0" class="text-xs text-muted-foreground"> no role </span>
            </div>
        </div>
    </div>
</template>
