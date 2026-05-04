import { Badge } from '@/components/ui/badge';
import { DataTableRowActions } from '@/components/ui/data-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { h } from 'vue';

export interface UserItem {
    id: number;
    nama: string;
    username: string;
    role: string | null;
    created_at: string | null;
}

function roleVariant(role: string | null): 'default' | 'secondary' | 'outline' {
    const normalizedRole = (role ?? '').toLowerCase();

    if (normalizedRole === 'admin') {
        return 'default';
    }

    if (normalizedRole === 'operator') {
        return 'secondary';
    }

    return 'outline';
}

export const getUserColumns = (actions: {
    onEdit: (user: UserItem) => void;
    onDelete: (user: UserItem) => void;
}): ColumnDef<UserItem>[] => [
    {
        id: 'spacer-left',
        header: '',
        cell: () => h('span', { class: 'block w-1' }),
        enableSorting: false,
        enableColumnFilter: false,
        meta: { headerClass: 'w-2 p-0', cellClass: 'w-2 p-0' },
    },
    {
        accessorKey: 'nama',
        header: 'Nama',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'font-medium text-foreground' }, row.original.nama),
        meta: { cellClass: 'pl-4' },
    },
    {
        accessorKey: 'role',
        header: 'Role',
        enableSorting: false,
        cell: ({ row }) =>
            h(
                Badge,
                {
                    variant: roleVariant(row.original.role),
                    class: 'text-xs capitalize',
                },
                () => row.original.role ?? 'viewer',
            ),
    },
    {
        id: 'actions',
        header: '',
        cell: ({ row }) =>
            h(DataTableRowActions, {
                actions: [
                    {
                        label: 'Edit User',
                        icon: Pencil,
                        onClick: () => {
                            actions.onEdit(row.original);
                        },
                    },
                    {
                        label: 'Hapus User',
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
