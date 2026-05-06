import { Badge } from '@/components/ui/badge';
import { DataTableRowActions } from '@/components/ui/data-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { Pencil, Trash2, Plus, Minus } from 'lucide-vue-next';
import { h } from 'vue';

export interface MitraItem {
    id: number;
    nik: string;
    nama_lengkap: string;
    email: string;
    jenis_kelamin: 'Laki-laki' | 'Perempuan';
    kode_kec: string;
    nama_kec: string;
    kode_desa: string;
    nama_desa: string;
    nomor_whatsapp: string;
    status_sobat: 'Sudah' | 'Belum';
    status_wawancara: 'Belum Wawancara' | 'Sudah Wawancara';
    status_kelulusan: 'Lulus' | 'Belum Lulus';
    tanggal_lahir: string;
    tempat_lahir: string;
    status_perkawinan: 'Belum Kawin' | 'Kawin' | 'Cerai Hidup' | 'Cerai Mati';
    pendidikan_terakhir: 'SLTP/Kebawah' | 'SLTA' | 'DI/DII/DII' | 'DIV/S1/S2/S3';
    pekerjaan: string;
    alamat_lengkap: string;
    riwayat_kegiatan_bps: string | null;
    created_at: string | null;
}

function interviewVariant(value: MitraItem['status_wawancara']): 'default' | 'secondary' {
    return value === 'Sudah Wawancara' ? 'default' : 'secondary';
}

function graduationVariant(value: MitraItem['status_kelulusan']): 'default' | 'secondary' {
    return value === 'Lulus' ? 'default' : 'secondary';
}

export const getMitraColumns = (actions: {
    onEdit: (mitra: MitraItem) => void;
    onDelete: (mitra: MitraItem) => void;
}): ColumnDef<MitraItem>[] => [
    {
        id: 'expander',
        header: '',
        cell: ({ row }) => {
            return h(
                'button',
                {
                    class: 'cursor-pointer flex h-6 w-6 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50',
                    onClick: (e: MouseEvent) => {
                        e.stopPropagation();
                        row.toggleExpanded();
                    },
                },
                [h(row.getIsExpanded() ? Minus : Plus, { class: 'h-3.5 w-3.5' })]
            );
        },
        enableSorting: false,
        enableColumnFilter: false,
        meta: { headerClass: 'w-10', cellClass: 'w-10' },
    },
    {
        accessorKey: 'nik',
        header: 'NIK',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'text-sm font-medium text-foreground' }, row.original.nik),
    },
    {
        accessorKey: 'nama_lengkap',
        header: 'Nama',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'text-sm font-medium text-foreground' }, row.original.nama_lengkap),
    },
    {
        accessorKey: 'jenis_kelamin',
        header: 'Jenis Kelamin',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.original.jenis_kelamin === 'Laki-laki' ? 'L' : 'P'),
        meta: { hideOnMobile: true },
    },
    {
        accessorKey: 'nama_kec',
        header: 'Kecamatan',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.original.nama_kec ?? row.original.kode_kec),
        meta: { hideOnMobile: true },
    },
    {
        accessorKey: 'nama_desa',
        header: 'Desa',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.original.nama_desa ?? row.original.kode_desa),
        meta: { hideOnMobile: true },
    },
    {
        accessorKey: 'nomor_whatsapp',
        header: 'WhatsApp',
        enableSorting: true,
        cell: ({ row }) => {
            const raw = row.original.nomor_whatsapp || '';
            let wa = raw.replace(/\D/g, '');
            if (wa.startsWith('0')) wa = '62' + wa.slice(1);
            return h(
                'a',
                {
                    href: `https://wa.me/${wa}`,
                    target: '_blank',
                    rel: 'noopener noreferrer',
                    class: 'inline-flex items-center gap-1.5 text-sm font-medium text-[#25D366] hover:text-[#1da851] transition',
                    onClick: (e: MouseEvent) => e.stopPropagation(),
                },
                [
                    h('svg', {
                        xmlns: 'http://www.w3.org/2000/svg',
                        width: '16',
                        height: '16',
                        viewBox: '0 0 24 24',
                        fill: 'currentColor',
                        class: 'shrink-0',
                    }, [
                        h('path', {
                            d: 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z'
                        })
                    ]),
                    raw
                ]
            );
        },
    },
    {
        accessorKey: 'status_kelulusan',
        header: 'Status',
        enableSorting: true,
        cell: ({ row }) =>
            h(Badge, { variant: graduationVariant(row.original.status_kelulusan), class: 'text-xs' }, () => row.original.status_kelulusan),
    },
    {
        id: 'actions',
        header: '',
        cell: ({ row }) =>
            h(DataTableRowActions, {
                actions: [
                    {
                        label: 'Edit Mitra',
                        icon: Pencil,
                        onClick: () => {
                            actions.onEdit(row.original);
                        },
                    },
                    {
                        label: 'Hapus Mitra',
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
