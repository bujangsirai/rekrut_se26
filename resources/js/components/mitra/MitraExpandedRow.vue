<script setup lang="ts">
import type { MitraItem } from './mitra-columns';
import { Button } from '@/components/ui/button';
import { FileImage } from 'lucide-vue-next';

defineProps<{
    original: MitraItem;
}>();
</script>

<template>
    <div class="grid gap-3 py-2 text-sm md:grid-cols-2">
        <div class="col-span-full mb-2 flex flex-wrap items-center justify-end gap-2">
            <a v-if="original.url_ktp" :href="`/admin/file/${original.url_ktp}`" target="_blank" rel="noopener noreferrer">
                <Button variant="outline" size="sm" class="h-8 gap-1.5 cursor-pointer">
                    <FileImage class="h-4 w-4" />
                    <span>File KTP</span>
                </Button>
            </a>
            <a v-if="original.url_spek_hp" :href="`/admin/file/${original.url_spek_hp}`" target="_blank" rel="noopener noreferrer">
                <Button variant="outline" size="sm" class="h-8 gap-1.5 cursor-pointer">
                    <FileImage class="h-4 w-4" />
                    <span>File Spek HP</span>
                </Button>
            </a>
            <a v-if="original.url_follow_ig" :href="`/admin/file/${original.url_follow_ig}`" target="_blank" rel="noopener noreferrer">
                <Button variant="outline" size="sm" class="h-8 gap-1.5 cursor-pointer">
                    <FileImage class="h-4 w-4" />
                    <span>File Follow IG</span>
                </Button>
            </a>
        </div>
        <div>
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Email</p>
            <p class="font-medium text-foreground">{{ original.email }}</p>
        </div>
        <div>
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Jenis Kelamin</p>
            <p class="font-medium text-foreground">{{ original.jenis_kelamin }}</p>
        </div>
        <div>
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">TTL</p>
            <p class="font-medium text-foreground">{{ original.tempat_lahir }}, {{ original.tanggal_lahir }}</p>
        </div>
        <div>
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Pendidikan</p>
            <p class="font-medium text-foreground">{{ original.pendidikan_terakhir }}</p>
        </div>
        <div>
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Status Perkawinan</p>
            <p class="font-medium text-foreground">{{ original.status_perkawinan }}</p>
        </div>
        <div>
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Pekerjaan</p>
            <p class="font-medium text-foreground">{{ original.pekerjaan }}</p>
        </div>
        <div>
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Merek HP</p>
            <p class="font-medium text-foreground">{{ original.merk_hp }}</p>
        </div>
        <div>
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Status Sobat</p>
            <p class="font-medium text-foreground">{{ original.status_sobat }}</p>
        </div>
        <div class="md:col-span-2">
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Alamat Lengkap</p>
            <p class="font-medium text-foreground">{{ original.alamat_lengkap }}</p>
        </div>
        <div v-if="original.is_domksb">
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Kecamatan Domisili</p>
            <p class="font-medium text-foreground">{{ original.kode_kec_dom }}</p>
        </div>
        <div v-if="original.is_domksb">
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Desa Domisili</p>
            <p class="font-medium text-foreground">{{ original.kode_desa_dom }}</p>
        </div>
        <div class="md:col-span-2">
            <p class="text-[11px] font-semibold text-muted-foreground uppercase">Riwayat Kegiatan BPS</p>
            <p class="font-medium text-foreground text-wrap">{{ original.riwayat_kegiatan_bps || '-' }}</p>
        </div>
        <div class="flex flex-wrap gap-2 md:col-span-2">
            <Badge variant="outline" :class="original.is_not_asn ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'">
                {{ original.is_not_asn ? 'Bukan ASN' : 'ASN/TNI/Polri' }}
            </Badge>
            <Badge v-if="original.jenis_kelamin === 'Perempuan'" variant="outline" :class="original.is_not_hamil ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'">
                {{ original.is_not_hamil ? 'Tidak Hamil' : 'Sedang Hamil' }}
            </Badge>
            <Badge variant="outline" :class="original.is_motor ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'">
                {{ original.is_motor ? 'Memiliki Motor' : 'Tidak Memiliki Motor' }}
            </Badge>
            <Badge variant="outline" :class="original.is_domksb ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'">
                {{ original.is_domksb ? 'Domisili KSB' : 'Luar KSB' }}
            </Badge>
        </div>
    </div>
</template>
