import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { DataTableRowActions } from '@/components/ui/data-table';
import type { RowAction } from '@/components/ui/data-table/DataTableRowActions.vue';
import { media } from '@/lib/media';
import type { ColumnDef } from '@tanstack/vue-table';
import { KeyRound, Minus, Plus, ShieldCheck } from 'lucide-vue-next';
import { h } from 'vue';

export interface UserItem {
    id: number;
    nama: string;
    nip: string;
    nip_baru: string | null;
    username: string;
    email_bps: string | null;
    email_gmail: string | null;
    golongan: string | null;
    jabatan: string | null;
    status_pegawai: string | null;
    url_foto: string | null;
    roles: string[];
}

/** Capitalize semua kata */
function capitalize(str: string): string {
    return str
        .split(' ')
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase())
        .join(' ');
}

/** Cek apakah status termasuk "aktif" */
function isAktif(status: string | null): boolean {
    if (!status) return false;
    const lower = status.toLowerCase();
    return lower.includes('aktif') && !lower.includes('tidak') && !lower.includes('non');
}

function renderStatusBadge(status: string | null) {
    if (!status) return h('span', { class: 'text-xs text-muted-foreground' }, '-');
    const active = isAktif(status);
    return h(
        Badge,
        {
            variant: 'outline',
            class: active
                ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-[11px]'
                : 'border-destructive/30 bg-destructive/10 text-destructive text-[11px]',
        },
        () => capitalize(status),
    );
}

function userActions(
    userId: number,
    onEditPassword: (id: number) => void,
    onEditProfile: (id: number) => void,
): RowAction[] {
    return [
        {
            label: 'Ubah Profil',
            icon: ShieldCheck,
            separator: true,
            onClick: () => onEditProfile(userId),
        },
        {
            label: 'Ubah Password',
            icon: KeyRound,
            onClick: () => onEditPassword(userId),
        },

    ];
}

export const getUserColumns = (actions: {
    onEditPassword: (id: number) => void;
    onEditProfile: (id: number) => void;
}): ColumnDef<UserItem>[] => [
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

        // Kolom 2: Avatar + Nama
        {
            accessorKey: 'nama',
            header: 'Nama Pegawai',
            cell: ({ row }) =>
                h('div', { class: 'flex items-center gap-2.5' }, [
                    h(Avatar, { class: 'h-8 w-8 shrink-0 border border-border' }, () => [
                        h('img', {
                            src: row.original.url_foto
                                ? media + row.original.url_foto
                                : media + 'img/avatar/default.png',
                            alt: row.original.nama,
                            class: 'h-full w-full object-cover',
                            onError: (e: Event) => {
                                (e.target as HTMLImageElement).src =
                                    media + 'img/avatar/default.png';
                            },
                        }),
                        h(AvatarFallback, { class: 'text-[10px]' }, () => ''),
                    ]),
                    h('span', { class: 'truncate text-xs font-medium' }, row.original.nama),
                ]),
        },

        // Kolom 3: Username
        {
            accessorKey: 'username',
            header: 'Username',
            cell: ({ row }) => h('span', { class: 'text-foreground' }, row.original.username),
            meta: { hideOnMobile: true },
        },

        // Kolom 4: Email Gmail
        {
            accessorKey: 'email_gmail',
            header: 'Email',
            cell: ({ row }) => h('span', { class: 'text-foreground' }, row.original.email_gmail ?? '-'),
            meta: { hideOnMobile: true },
            enableSorting: false,
        },

        // Kolom 3: Status pegawai (badge)
        {
            accessorKey: 'status_pegawai',
            header: 'Status',
            cell: ({ row }) => renderStatusBadge(row.original.status_pegawai),
            meta: { hideOnMobile: true },
        },

        // Kolom aksi
        {
            id: 'actions',
            header: '',
            cell: ({ row }) =>
                h(DataTableRowActions, {
                    actions: userActions(row.original.id, actions.onEditPassword, actions.onEditProfile),
                }),
            enableSorting: false,
            enableColumnFilter: false,
            meta: { headerClass: 'w-10', cellClass: 'w-10' },
        },
    ];
