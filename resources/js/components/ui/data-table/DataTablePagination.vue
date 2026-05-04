<script setup lang="ts">
import { computed } from 'vue';
import type { Table } from '@tanstack/vue-table';
import {
    ChevronLeft,
    ChevronRight,
    ChevronDown,
} from 'lucide-vue-next';

const props = defineProps<{
    table: Table<any>;
}>();

const pageIndex = computed(() => props.table.getState().pagination.pageIndex);
const pageCount = computed(() => props.table.getPageCount() || 1);

const paginationArray = computed(() => {
    const total = pageCount.value;
    const current = pageIndex.value;
    
    // Jika total halaman sedikit, tampilkan semua (max 5)
    if (total <= 5) {
        return Array.from({ length: total }, (_, i) => i);
    }
    
    // Jika di awal
    if (current < 3) {
        return [0, 1, 2, '...', total - 1];
    }
    
    // Jika di akhir
    if (current > total - 4) {
        return [0, '...', total - 3, total - 2, total - 1];
    }
    
    // Jika di tengah
    return [0, '...', current, '...', total - 1];
});
</script>

<template>
    <div class="flex items-center justify-center w-full py-1">
        <div class="flex flex-row items-center gap-3 sm:gap-4 bg-background rounded-full border border-border px-2 sm:px-3 py-1 shadow-sm">
            
            <!-- Pages Navigation -->
            <div class="flex items-center gap-0.5 sm:gap-1">
                <!-- Prev Button -->
                <button 
                    @click="table.previousPage()" 
                    :disabled="!table.getCanPreviousPage()"
                    class="h-6 w-6 flex items-center justify-center rounded-full text-foreground hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-colors"
                >
                    <ChevronLeft class="h-3.5 w-3.5" />
                </button>

                <!-- Pages Numbers -->
                <div class="flex items-center">
                    <template v-for="(p, i) in paginationArray" :key="i">
                        <span v-if="p === '...'" class="px-0.5 sm:px-1 text-muted-foreground text-xs tracking-widest">...</span>
                        <button
                            v-else
                            @click="table.setPageIndex(p as number)"
                            class="h-6 w-6 sm:h-7 sm:w-7 flex items-center justify-center rounded-full text-xs transition-colors cursor-pointer font-medium"
                            :class="p === pageIndex ? 'bg-indigo-50 text-indigo-600 border border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800' : 'text-foreground hover:bg-muted border border-transparent'"
                        >
                            {{ (p as number) + 1 }}
                        </button>
                    </template>
                </div>

                <!-- Next Button -->
                <button 
                    @click="table.nextPage()" 
                    :disabled="!table.getCanNextPage()"
                    class="h-6 w-6 flex items-center justify-center rounded-full text-foreground hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-colors"
                >
                    <ChevronRight class="h-3.5 w-3.5" />
                </button>
            </div>

            <!-- Page Size Dropdown -->
            <div class="relative flex items-center border border-border rounded-full hover:bg-muted transition-colors">
                <select
                    class="h-6 sm:h-7 appearance-none bg-transparent pl-2.5 pr-7 py-0.5 text-xs outline-none cursor-pointer text-foreground rounded-full w-full font-medium shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring"
                    :value="table.getState().pagination.pageSize"
                    @change="table.setPageSize(Number(($event.target as HTMLSelectElement).value))"
                >
                    <option v-for="pageSize in [10, 25, 50, 100]" :key="pageSize" :value="pageSize">
                        {{ pageSize }} / page
                    </option>
                </select>
                <ChevronDown class="absolute right-2 top-1/2 -translate-y-1/2 h-3.5 w-3.5 pointer-events-none text-muted-foreground" />
            </div>

        </div>
    </div>
</template>
