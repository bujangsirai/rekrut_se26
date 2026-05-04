import { Badge } from '@/components/ui/badge';
import { DataTableRowActions } from '@/components/ui/data-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { h } from 'vue';

export interface RoleItem {
    id: number;
    name: string;
    description: string | null;
    users_count: number;
    created_at: string | null;
}

function roleVariant(role: string): 'default' | 'secondary' | 'outline' {
    const normalizedRole = role.toLowerCase();
    if (normalizedRole === 'admin' || normalizedRole === 'superadmin') {
        return 'default';
    }

    if (normalizedRole === 'operator') {
        return 'secondary';
    }

    return 'outline';
}

export const getRoleColumns = (actions: {
    onEdit: (role: RoleItem) => void;
    onDelete: (role: RoleItem) => void;
}): ColumnDef<RoleItem>[] => [
    {
        id: 'spacer-left',
        header: '',
        cell: () => h('span', { class: 'block w-1' }),
        enableSorting: false,
        enableColumnFilter: false,
        meta: { headerClass: 'w-2 p-0', cellClass: 'w-2 p-0' },
    },
    {
        accessorKey: 'name',
        header: 'Nama Role',
        enableSorting: true,
        cell: ({ row }) => h(Badge, { variant: roleVariant(row.original.name), class: 'text-xs' }, () => row.original.name),
        meta: { cellClass: 'pl-4' },
    },
    {
        accessorKey: 'description',
        header: 'Deskripsi',
        enableSorting: false,
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.original.description ?? '-'),
    },
    {
        accessorKey: 'users_count',
        header: 'Jumlah User',
        enableSorting: false,
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, `${row.original.users_count} user`),
    },
    {
        id: 'actions',
        header: '',
        cell: ({ row }) =>
            h(DataTableRowActions, {
                actions: [
                    {
                        label: 'Edit Role',
                        icon: Pencil,
                        onClick: () => {
                            actions.onEdit(row.original);
                        },
                    },
                    {
                        label: 'Hapus Role',
                        icon: Trash2,
                        destructive: true,
                        separator: true,
                        onClick: () => {
                            actions.onDelete(row.original);
                        },
                    },
                ],
            }),
        enableSorting: false,
        enableColumnFilter: false,
        meta: { headerClass: 'w-10 pr-3', cellClass: 'w-10 pr-3' },
    },
    {
        id: 'spacer-right',
        header: '',
        cell: () => h('span', { class: 'block w-1' }),
        enableSorting: false,
        enableColumnFilter: false,
        meta: { headerClass: 'w-2 p-0', cellClass: 'w-2 p-0' },
    },
];
