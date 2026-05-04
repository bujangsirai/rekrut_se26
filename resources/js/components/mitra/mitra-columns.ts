import { Badge } from '@/components/ui/badge';
import { DataTableRowActions } from '@/components/ui/data-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { h } from 'vue';

export interface MitraItem {
    id: number;
    nama_lengkap: string;
    email: string;
    jenis_kelamin: 'Laki-laki' | 'Perempuan';
    kode_kec: string;
    kode_desa: string;
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
        accessorKey: 'nama_lengkap',
        header: 'Nama Mitra',
        enableSorting: true,
        cell: ({ row }) => h('span', { class: 'font-medium text-foreground' }, row.original.nama_lengkap),
    },
    {
        accessorKey: 'nomor_whatsapp',
        header: 'WhatsApp',
        enableSorting: false,
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.original.nomor_whatsapp),
    },
    {
        accessorKey: 'status_wawancara',
        header: 'Status Wawancara',
        enableSorting: false,
        cell: ({ row }) =>
            h(Badge, { variant: interviewVariant(row.original.status_wawancara), class: 'text-xs' }, () => row.original.status_wawancara),
    },
    {
        accessorKey: 'status_kelulusan',
        header: 'Status Kelulusan',
        enableSorting: false,
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
