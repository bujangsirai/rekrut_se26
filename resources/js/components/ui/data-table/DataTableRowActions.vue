<script setup lang="ts">
import { MoreHorizontal } from 'lucide-vue-next';
import type { Component } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export interface RowAction {
    label: string;
    icon?: Component;
    destructive?: boolean;
    separator?: boolean;
    onClick: () => void;
}

defineProps<{
    actions: RowAction[];
}>();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child @click.stop>
            <Button
                variant="ghost"
                size="icon"
                class="h-7 w-7 text-muted-foreground data-[state=open]:bg-muted cursor-pointer hover:bg-muted"
            >
                <MoreHorizontal class="h-4 w-4" />
                <span class="sr-only">Buka menu</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-44">
            <template v-for="(action, i) in actions" :key="i">
                <DropdownMenuSeparator v-if="action.separator && i > 0" />
                <DropdownMenuItem
                    :class="[
                        'gap-2 text-sm cursor-pointer',
                        action.destructive
                            ? 'text-destructive focus:bg-destructive/10 focus:text-destructive'
                            : '',
                    ]"
                    @click.stop="action.onClick()"
                >
                    <component :is="action.icon" v-if="action.icon" class="h-3.5 w-3.5" />
                    {{ action.label }}
                </DropdownMenuItem>
            </template>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
