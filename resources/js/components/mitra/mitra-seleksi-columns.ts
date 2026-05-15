import { Badge } from '@/components/ui/badge';
import { DataTableRowActions } from '@/components/ui/data-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { Pencil, Trash2, Plus, Minus, Mars, Venus, ChevronDown } from 'lucide-vue-next';
import { h } from 'vue';

export interface MitraItem {
    id: number;
    nik: string;
    url_ktp: string | null;
    url_spek_hp: string | null;
    url_follow_ig: string | null;
    url_upload_sobat: string | null;
    kode_akses: string;
    nama_lengkap: string;
    email: string;
    jenis_kelamin: 'Laki-laki' | 'Perempuan';
    kode_kec: string;
    nama_kec: string;
    kode_desa: string;
    nama_desa: string;
    nomor_whatsapp: string;
    status_sobat: 'Sudah' | 'Belum';
    is_mitrakepka: boolean;
    status_wawancara: 'Belum Wawancara' | 'Sudah Wawancara';
    status_kelulusan: 'Lulus' | 'Belum Lulus';
    jawaban_kuesioner: Record<string, unknown> | null;
    skor_kuesioner: number | null;
    tanggal_lahir: string;
    tempat_lahir: string;
    status_perkawinan: 'Belum Kawin' | 'Kawin' | 'Cerai Hidup' | 'Cerai Mati';
    pendidikan_terakhir: 'SLTP/Kebawah' | 'SLTA' | 'DI/DII/DII' | 'DIV/S1/S2/S3';
    pekerjaan: string;
    alamat_lengkap: string;
    riwayat_kegiatan_bps: string | null;
    is_domksb: boolean;
    is_motor: boolean;
    is_not_asn: boolean;
    is_not_hamil: boolean;
    merk_hp: string;
    kode_kec_dom: string | null;
    kode_desa_dom: string | null;
    created_at: string | null;
}

function interviewVariant(value: MitraItem['status_wawancara']): 'default' | 'secondary' {
    return value === 'Sudah Wawancara' ? 'default' : 'secondary';
}

function sobatVariant(value: MitraItem['status_sobat']): 'default' | 'secondary' {
    return value === 'Sudah' ? 'default' : 'secondary';
}

function graduationVariant(value: MitraItem['status_kelulusan']): 'default' | 'secondary' {
    return value === 'Lulus' ? 'default' : 'secondary';
}

function wawancaraLabel(value: MitraItem['status_wawancara']): 'Sudah' | 'Belum' {
    return value === 'Sudah Wawancara' ? 'Sudah' : 'Belum';
}

function kelulusanLabel(value: MitraItem['status_kelulusan']): 'Sudah' | 'Belum' {
    return value === 'Lulus' ? 'Sudah' : 'Belum';
}

function statusBadgeClass(label: 'Sudah' | 'Belum'): string {
    return label === 'Sudah'
        ? 'border-emerald-400 bg-emerald-100 text-emerald-900'
        : 'border-amber-400 bg-amber-100 text-amber-900';
}

const statusPillCompactClass = 'px-2';
const isSobatEditable = false;
const isMitraKepkaEditable = false;

export const getMitraColumns = (actions: {
    onEdit: (mitra: MitraItem) => void;
    onDelete: (mitra: MitraItem) => void;
    onChangeSobat?: (mitra: MitraItem, statusSobat: MitraItem['status_sobat']) => void;
    isSobatUpdating?: (mitraId: number) => boolean;
    onChangeMitraKepka?: (mitra: MitraItem, isMitraKepka: boolean) => void;
    isMitraKepkaUpdating?: (mitraId: number) => boolean;
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
        accessorKey: 'nama_lengkap',
        header: () => h('span', { class: 'text-xs font-semibold' }, 'Mitra'),
        enableSorting: true,
        cell: ({ row }) =>
            h('div', { class: 'leading-tight' }, [
                h('p', { class: 'text-xs font-semibold text-foreground' }, row.original.nama_lengkap),
                h('p', { class: 'mt-0.5 text-[11px] text-muted-foreground' }, row.original.nik),
            ]),
        meta: { cellClass: 'min-w-[220px]' },
    },
    {
        accessorKey: 'jenis_kelamin',
        header: () => h('span', { class: 'text-xs font-semibold' }, 'JK'),
        enableSorting: true,
        cell: ({ row }) => {
            const isMale = row.original.jenis_kelamin === 'Laki-laki';

            return h(
                'span',
                {
                    class: isMale
                        ? 'inline-flex items-center gap-1 rounded-md bg-blue-50 px-1.5 py-0.5 text-[11px] font-medium text-blue-700'
                        : 'inline-flex items-center gap-1 rounded-md bg-pink-50 px-1.5 py-0.5 text-[11px] font-medium text-pink-700',
                },
                [
                    h(isMale ? Mars : Venus, { class: 'h-3.5 w-3.5' }),
                    isMale ? 'L' : 'P',
                ],
            );
        },
        meta: { hideOnMobile: true },
    },
    {
        accessorKey: 'nama_kec',
        header: () => h('span', { class: 'text-xs font-semibold' }, 'Domisili'),
        enableSorting: true,
        cell: ({ row }) =>
            h('div', { class: 'leading-tight' }, [
                h('p', { class: 'text-xs font-semibold text-foreground' }, row.original.nama_kec ?? row.original.kode_kec),
                h('p', { class: 'mt-0.5 text-[11px] text-muted-foreground' }, row.original.nama_desa ?? row.original.kode_desa),
            ]),
        meta: { hideOnMobile: true, cellClass: 'min-w-[150px]' },
    },
    {
        accessorKey: 'nomor_whatsapp',
        header: () => h('span', { class: 'text-xs font-semibold' }, 'WA'),
        enableSorting: false,
        cell: ({ row }) => {
            const raw = row.original.nomor_whatsapp || '';
            let wa = raw.replace(/\D/g, '');
            if (wa.startsWith('0')) wa = '62' + wa.slice(1);
            return h(
                'a',
                {
                    href: `https://wa.me/${wa}`,
                    title: raw,
                    target: '_blank',
                    rel: 'noopener noreferrer',
                    class: 'inline-flex items-center gap-1 text-xs font-medium text-[#25D366] transition hover:text-[#1da851]',
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
                    ])
                ]
            );
        },
    },
    {
        accessorKey: 'status_sobat',
        header: () => h('span', { class: 'text-xs font-semibold' }, 'Sobat'),
        enableSorting: true,
        cell: ({ row }) => {
            const label = row.original.status_sobat === 'Sudah' ? 'Sudah' : 'Belum';
            const isUpdating = actions.isSobatUpdating?.(row.original.id) ?? false;

            if (!isSobatEditable || !actions.onChangeSobat) {
                return h(Badge, { variant: 'outline', class: `text-[11px] inline-flex ${statusPillCompactClass} ${statusBadgeClass(label)}` }, () => label);
            }

            return h('div', { class: 'relative inline-flex items-center' }, [
                h(
                    'select',
                    {
                        value: row.original.status_sobat,
                        disabled: isUpdating,
                        class: `h-7 min-w-[68px] appearance-none cursor-pointer rounded-full border pl-2 pr-5 text-[11px] font-medium outline-none ${
                            statusBadgeClass(label)
                        } ${isUpdating ? 'cursor-not-allowed opacity-60' : ''}`,
                        onClick: (e: MouseEvent) => e.stopPropagation(),
                        onChange: (e: Event) => {
                            const target = e.target as HTMLSelectElement;
                            actions.onChangeSobat?.(
                                row.original,
                                (target.value === 'Sudah' ? 'Sudah' : 'Belum') as MitraItem['status_sobat'],
                            );
                        },
                    },
                    [
                        h('option', { value: 'Belum' }, 'Belum'),
                        h('option', { value: 'Sudah' }, 'Sudah'),
                    ],
                ),
                h(ChevronDown, { class: 'pointer-events-none absolute right-2 h-3.5 w-3.5' }),
            ]);
        },
        meta: { hideOnMobile: true },
    },
    {
        accessorKey: 'is_mitrakepka',
        header: () => h('span', { class: 'text-xs font-semibold' }, 'KEPKA'),
        enableSorting: true,
        cell: ({ row }) => {
            const label = row.original.is_mitrakepka ? 'Ya' : 'Tidak';
            const isUpdating = actions.isMitraKepkaUpdating?.(row.original.id) ?? false;

            if (!isMitraKepkaEditable || !actions.onChangeMitraKepka) {
                return h(Badge, { variant: 'outline', class: `text-[11px] inline-flex ${statusPillCompactClass} ${statusBadgeClass(row.original.is_mitrakepka ? 'Sudah' : 'Belum')}` }, () => label);
            }

            return h('div', { class: 'relative inline-flex items-center' }, [
                h(
                    'select',
                    {
                        value: row.original.is_mitrakepka ? '1' : '0',
                        disabled: isUpdating,
                        class: `h-7 min-w-[68px] appearance-none cursor-pointer rounded-full border pl-2 pr-5 text-[11px] font-medium outline-none ${
                            statusBadgeClass(row.original.is_mitrakepka ? 'Sudah' : 'Belum')
                        } ${isUpdating ? 'cursor-not-allowed opacity-60' : ''}`,
                        onClick: (e: MouseEvent) => e.stopPropagation(),
                        onChange: (e: Event) => {
                            const target = e.target as HTMLSelectElement;
                            actions.onChangeMitraKepka?.(row.original, target.value === '1');
                        },
                    },
                    [
                        h('option', { value: '0' }, 'Tidak'),
                        h('option', { value: '1' }, 'Ya'),
                    ],
                ),
                h(ChevronDown, { class: 'pointer-events-none absolute right-2 h-3.5 w-3.5' }),
            ]);
        },
        meta: { hideOnMobile: true },
    },
    {
        accessorKey: 'status_wawancara',
        header: () => h('span', { class: 'text-xs font-semibold' }, 'Wawancara'),
        enableSorting: true,
        cell: ({ row }) => {
            const label = wawancaraLabel(row.original.status_wawancara);
            return h(Badge, { variant: 'outline', class: `text-[11px] inline-flex ${statusPillCompactClass} ${statusBadgeClass(label)}` }, () => label);
        },
        meta: { hideOnMobile: true },
    },
    {
        accessorKey: 'status_kelulusan',
        header: () => h('span', { class: 'text-xs font-semibold' }, 'Lulus'),
        enableSorting: true,
        cell: ({ row }) => {
            const label = kelulusanLabel(row.original.status_kelulusan);
            return h(Badge, { variant: 'outline', class: `text-[11px] inline-flex ${statusPillCompactClass} ${statusBadgeClass(label)}` }, () => label);
        },
    },
    {
        accessorKey: 'skor_kuesioner',
        header: () => h('span', { class: 'text-xs font-semibold' }, 'Skore'),
        enableSorting: true,
        cell: ({ row }) => {
            if (row.original.skor_kuesioner === null) {
                return null;
            }

            return h('span', { class: 'text-xs font-semibold text-slate-800' }, String(row.original.skor_kuesioner));
        },
        meta: { hideOnMobile: true },
    },
    {
        id: 'periksa_mitra',
        header: () => h('span', { class: 'text-xs font-semibold' }, 'Penilaian'),
        cell: ({ row }) => {
            if (row.original.jawaban_kuesioner === null) {
                return null;
            }

            return h(
                'a',
                {
                    href: `/admin/penilaian/${row.original.kode_akses}`,
                    target: '_blank',
                    rel: 'noopener noreferrer',
                    class: 'inline-flex items-center text-green-600 transition hover:text-green-700',
                    title: 'Penilaian',
                },
                [h(Pencil, { class: 'h-4 w-4' })],
            );
        },
        enableSorting: false,
        enableColumnFilter: false,
        meta: { hideOnMobile: true, cellClass: 'min-w-[80px]' },
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
