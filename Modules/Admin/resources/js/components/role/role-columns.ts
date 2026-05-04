import { Badge } from '@/components/ui/badge';
import { DataTableRowActions } from '@/components/ui/data-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { Minus, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { h } from 'vue';

export interface RoleItem {
    id: number;
    name: string;
    description: string | null;
    users_count: number;
    users: {
        id: number;
        nama: string;
        nip_baru: string | null;
        email_bps: string | null;
        email_gmail: string | null;
        status_pegawai: string | null;
        url_foto: string | null;
    }[];
}

function roleVariant(role: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    if (role === 'Superadmin') return 'default';
    return 'secondary';
}

export const getRoleColumns = (actions: {
    onEdit: (role: RoleItem) => void;
    onDelete: (role: RoleItem) => void;
}): ColumnDef<RoleItem>[] => [
        // Kolom 1: Toggle expand (Plus/Minus bulat)
        {
            id: 'expander',
            header: '',
            cell: ({ row }) =>
                h(
                    'button',
                    {
                        class: [
                            'flex h-6 w-6 items-center justify-center rounded-full border transition-colors cursor-pointer',
                            row.getIsExpanded()
                                ? 'border-foreground/30 bg-foreground/10 text-foreground'
                                : 'border-border bg-transparent text-muted-foreground hover:border-foreground/30 hover:bg-muted',
                        ],
                        onClick: (e: MouseEvent) => {
                            e.stopPropagation();
                            row.toggleExpanded();
                        },
                    },
                    [h(row.getIsExpanded() ? Minus : Plus, { class: 'h-3 w-3' })],
                ),
            enableSorting: false,
            enableColumnFilter: false,
            meta: { headerClass: 'w-10', cellClass: 'w-10' },
        },
        {
            accessorKey: 'name',
            header: 'Nama Role',
            enableSorting: true,
            cell: ({ row }) =>
                h('div', { class: 'flex items-center gap-2' }, [
                    h(Badge, { variant: roleVariant(row.original.name), class: 'text-xs' }, () =>
                        row.original.name,
                    ),
                ]),
        },
        {
            accessorKey: 'description',
            header: 'Deskripsi',
            enableSorting: false,
            meta: { hideOnMobile: true },
            cell: ({ row }) =>
                h('span', { class: 'text-xs text-muted-foreground', title: row.original.description ?? '' }, row.original.description ?? '-'),
        },
        {
            accessorKey: 'users_count',
            header: 'Jumlah User',
            enableSorting: false,
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-sm text-muted-foreground' },
                    `${row.original.users_count} user`,
                ),
            enableColumnFilter: false,
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
            meta: { headerClass: 'w-10', cellClass: 'w-10' },
        },
    ];
