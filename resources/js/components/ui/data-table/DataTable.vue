<script setup lang="ts" generic="TData, TValue">
import {
    type ColumnDef,
    type ColumnFiltersState,
    type ExpandedState,
    type FilterFn,
    type SortingState,
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { ArrowDown, ArrowUp, ChevronsUpDown, ListFilter, Search } from 'lucide-vue-next';
import { computed, ref, useSlots } from 'vue';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import DataTablePagination from './DataTablePagination.vue';

type ToolbarFilterOption = {
    label: string;
    value: string;
};

type ToolbarFilter = {
    key: string;
    label: string;
    options: ToolbarFilterOption[];
    placeholder?: string;
};

const props = withDefaults(
    defineProps<{
        data: TData[];
        columns: ColumnDef<TData, TValue>[];
        searchPlaceholder?: string;
        /** Search satu kolom spesifik (pakai accessorKey) */
        searchColumn?: string;
        /** Search banyak field dari row.original sekaligus */
        searchFields?: string[];
        pageSize?: number;
        singleExpandedRow?: boolean;
        toolbarFilters?: ToolbarFilter[];
        showRowAggregate?: boolean;
    }>(),
    {
        searchPlaceholder: 'Cari...',
        searchColumn: undefined,
        searchFields: undefined,
        pageSize: 10,
        singleExpandedRow: false,
        toolbarFilters: undefined,
        showRowAggregate: false,
    },
);

const slots = useSlots();
const hasExpandedRow = !!slots['expanded-row'];
const isMultiField = computed(() => !!props.searchFields?.length);

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const expanded = ref<ExpandedState>({});
const globalFilter = ref('');
const selectedToolbarFilters = ref<Record<string, string>>({});
const hasToolbarFilters = computed(() => (props.toolbarFilters?.length ?? 0) > 0);

const filteredData = computed(() => {
    if (!hasToolbarFilters.value) {
        return props.data;
    }

    return props.data.filter((item) => {
        const row = item as Record<string, unknown>;

        return (props.toolbarFilters ?? []).every((filterConfig) => {
            const selected = selectedToolbarFilters.value[filterConfig.key] ?? '';
            if (!selected) {
                return true;
            }

            if (selected === '__NULL__') {
                return row[filterConfig.key] == null || String(row[filterConfig.key]).trim() === '';
            }

            return String(row[filterConfig.key] ?? '') === selected;
        });
    });
});

function normalizeExpandedState(next: ExpandedState, prev: ExpandedState): ExpandedState {
    if (!props.singleExpandedRow || next === true || typeof next !== 'object') {
        return next;
    }

    const expandedKeys = Object.keys(next).filter((key) => next[key]);
    if (expandedKeys.length <= 1) {
        return next;
    }

    const previousExpanded = prev !== true && typeof prev === 'object' ? prev : {};
    const newlyOpenedKey = expandedKeys.find((key) => !previousExpanded[key]);
    const activeKey = newlyOpenedKey ?? expandedKeys[expandedKeys.length - 1];

    return { [activeKey]: true };
}

// Custom filter function untuk multi-field search
const multiFieldFilterFn: FilterFn<TData> = (row, _columnId, filterValue: string) => {
    const keyword = filterValue.trim().toLowerCase();
    if (!keyword) return true;
    const fields = props.searchFields ?? [];
    return fields.some((field) => {
        const value = (row.original as Record<string, unknown>)[field];
        // Support array field (misal: roles: ['admin', 'Superadmin'])
        if (Array.isArray(value)) {
            return value.some((v) => String(v ?? '').toLowerCase().includes(keyword));
        }
        return String(value ?? '').toLowerCase().includes(keyword);
    });
};

const table = useVueTable({
    get data() {
        return filteredData.value;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    ...(hasExpandedRow && {
        getExpandedRowModel: getExpandedRowModel(),
        getRowCanExpand: () => true,
    }),
    globalFilterFn: multiFieldFilterFn,
    onSortingChange: (updater) => {
        sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
    },
    onColumnFiltersChange: (updater) => {
        columnFilters.value =
            typeof updater === 'function' ? updater(columnFilters.value) : updater;
    },
    onGlobalFilterChange: (updater) => {
        globalFilter.value =
            typeof updater === 'function' ? updater(globalFilter.value) : updater;
    },
    onExpandedChange: (updater) => {
        const nextExpanded =
            typeof updater === 'function' ? updater(expanded.value) : updater;
        expanded.value = normalizeExpandedState(nextExpanded, expanded.value);
    },
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get globalFilter() {
            return globalFilter.value;
        },
        get expanded() {
            return expanded.value;
        },
    },
    initialState: {
        pagination: { pageSize: props.pageSize },
    },
});

// Untuk mode searchColumn: ambil kolom yang dituju
const firstFilterableColumn = table
    .getAllColumns()
    .find((col) => col.getCanFilter() && col.id !== 'actions' && col.id !== 'expander');

const activeSearchColumn = computed(() =>
    props.searchColumn ? table.getColumn(props.searchColumn) : firstFilterableColumn,
);

// Handler input search — pakai global filter atau column filter sesuai mode
function onSearchInput(value: string) {
    if (isMultiField.value) {
        table.setGlobalFilter(value);
    } else {
        activeSearchColumn.value?.setFilterValue(value);
    }
}

// Nilai yang ditampilkan di input
const searchValue = computed(() =>
    isMultiField.value
        ? globalFilter.value
        : ((activeSearchColumn.value?.getFilterValue() as string) ?? ''),
);

const showSearch = computed(() => isMultiField.value || !!activeSearchColumn.value);
const filteredRowsCount = computed(() => table.getFilteredRowModel().rows.length);
const totalRowsCount = computed(() => props.data.length);

function onToolbarFilterChange(key: string, value: string): void {
    selectedToolbarFilters.value = {
        ...selectedToolbarFilters.value,
        [key]: value,
    };
}
</script>

<template>
    <div class="space-y-3">
        <!-- Search & Actions -->
        <div v-if="showSearch || hasToolbarFilters || $slots.actions" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <div
                    v-if="showSearch"
                    class="flex w-full max-w-sm items-center gap-2 rounded-lg border border-input bg-background h-9 px-3 shadow-sm"
                >
                    <Search class="h-4 w-4 shrink-0 text-muted-foreground" />
                    <Input
                        :model-value="searchValue"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="border-0 bg-transparent h-full p-0 text-xs shadow-none focus-visible:ring-0 sm:text-sm"
                        @update:model-value="onSearchInput(String($event))"
                    />
                </div>
                <div v-if="hasToolbarFilters" class="flex flex-wrap items-center gap-2">
                    <div
                        v-for="filterConfig in (toolbarFilters ?? [])"
                        :key="filterConfig.key"
                        class="flex h-9 items-center rounded-lg border border-input bg-background px-2 shadow-sm"
                    >
                        <Select
                            :model-value="selectedToolbarFilters[filterConfig.key] || '__ALL__'"
                            @update:model-value="onToolbarFilterChange(filterConfig.key, String($event) === '__ALL__' ? '' : String($event ?? ''))"
                        >
                            <SelectTrigger class="h-full w-[240px] cursor-pointer border-0 bg-transparent px-1 py-0 text-xs shadow-none focus-visible:ring-0 sm:text-sm">
                                <span class="flex items-center gap-2">
                                    <ListFilter class="h-4 w-4 shrink-0 text-muted-foreground" />
                                    <SelectValue :placeholder="filterConfig.placeholder ?? `Semua ${filterConfig.label}`" />
                                </span>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__ALL__">
                                    {{ filterConfig.placeholder ?? `Semua ${filterConfig.label}` }}
                                </SelectItem>
                                <SelectItem
                                    v-for="option in filterConfig.options"
                                    :key="`${filterConfig.key}-${option.value}`"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>
            <div v-if="showRowAggregate || $slots.actions" class="flex items-center gap-2">
                <div
                    v-if="showRowAggregate"
                    class="inline-flex h-9 shrink-0 items-center justify-center whitespace-nowrap rounded-lg border border-input bg-background px-3 text-xs font-medium leading-none text-muted-foreground shadow-sm sm:text-sm"
                >
                    {{ filteredRowsCount }} / {{ totalRowsCount }} data
                </div>
                <slot name="actions" />
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-border shadow-sm">
            <Table>
                <TableHeader>
                    <tr
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                        class="border-b border-border bg-muted/40"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            :class="[
                                header.column.columnDef.meta?.hideOnMobile
                                    ? 'hidden md:table-cell'
                                    : '',
                                header.column.getCanSort()
                                    ? 'cursor-pointer select-none'
                                    : '',
                                header.column.columnDef.meta?.headerClass ?? '',
                            ]"
                            @click="
                                header.column.getCanSort() && header.column.toggleSorting()
                            "
                        >
                            <div
                                v-if="!header.isPlaceholder"
                                class="flex items-center gap-2"
                                :class="header.column.getCanSort() ? 'hover:text-foreground' : ''"
                            >
                                <FlexRender
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                                <template v-if="header.column.getCanSort()">
                                    <ArrowUp
                                        v-if="header.column.getIsSorted() === 'asc'"
                                        class="h-3.5 w-3.5 text-foreground"
                                    />
                                    <ArrowDown
                                        v-else-if="header.column.getIsSorted() === 'desc'"
                                        class="h-3.5 w-3.5 text-foreground"
                                    />
                                    <ChevronsUpDown
                                        v-else
                                        class="h-3.5 w-3.5 opacity-30 group-hover:opacity-100"
                                    />
                                </template>
                            </div>
                        </TableHead>
                    </tr>
                </TableHeader>

                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <template
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                        >
                            <!-- Data row -->
                            <TableRow
                                :data-state="row.getIsSelected() ? 'selected' : undefined"
                                :class="[
                                    hasExpandedRow && row.getIsExpanded()
                                        ? 'bg-muted/80'
                                        : '',
                                ]"
                            >
                                <TableCell
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                    :class="[
                                        cell.column.columnDef.meta?.hideOnMobile
                                            ? 'hidden md:table-cell'
                                            : '',
                                        cell.column.columnDef.meta?.cellClass ?? '',
                                    ]"
                                >
                                    <FlexRender
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </TableCell>
                            </TableRow>

                            <!-- Expanded row (opt-in via slot) -->
                            <tr
                                v-if="hasExpandedRow && row.getIsExpanded()"
                                :key="`${row.id}-expanded`"
                                class="border-b border-border"
                            >
                                <td
                                    :colspan="row.getVisibleCells().length"
                                    class="bg-muted/30 px-4 py-3"
                                >
                                    <slot
                                        name="expanded-row"
                                        :row="row"
                                        :original="row.original"
                                    />
                                </td>
                            </tr>
                        </template>
                    </template>

                    <TableRow v-else>
                        <TableCell
                            :colspan="columns.length"
                            class="h-20 text-center text-sm text-muted-foreground"
                        >
                            Tidak ada data.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <DataTablePagination :table="table" />
    </div>
</template>
