<script setup lang="ts">
import type { MitraItem } from './mitra-columns';

defineProps<{
    original: MitraItem;
}>();
</script>

<template>
    <div class="grid gap-3 py-2 text-sm md:grid-cols-2">
        <div>
            <p class="text-[11px] text-muted-foreground uppercase">Email</p>
            <p class="font-medium text-foreground">{{ original.email }}</p>
        </div>
        <div>
            <p class="text-[11px] text-muted-foreground uppercase">Jenis Kelamin</p>
            <p class="font-medium text-foreground">{{ original.jenis_kelamin }}</p>
        </div>
        <div>
            <p class="text-[11px] text-muted-foreground uppercase">TTL</p>
            <p class="font-medium text-foreground">{{ original.tempat_lahir }}, {{ original.tanggal_lahir }}</p>
        </div>
        <div>
            <p class="text-[11px] text-muted-foreground uppercase">Pendidikan</p>
            <p class="font-medium text-foreground">{{ original.pendidikan_terakhir }}</p>
        </div>
        <div>
            <p class="text-[11px] text-muted-foreground uppercase">Status Perkawinan</p>
            <p class="font-medium text-foreground">{{ original.status_perkawinan }}</p>
        </div>
        <div>
            <p class="text-[11px] text-muted-foreground uppercase">Pekerjaan</p>
            <p class="font-medium text-foreground">{{ original.pekerjaan }}</p>
        </div>
        <div>
            <p class="text-[11px] text-muted-foreground uppercase">Kode Kecamatan/Desa</p>
            <p class="font-medium text-foreground">{{ original.kode_kec }} / {{ original.kode_desa }}</p>
        </div>
        <div>
            <p class="text-[11px] text-muted-foreground uppercase">Status Sobat</p>
            <p class="font-medium text-foreground">{{ original.status_sobat }}</p>
        </div>
        <div class="md:col-span-2">
            <p class="text-[11px] text-muted-foreground uppercase">Alamat</p>
            <p class="font-medium text-foreground">{{ original.alamat_lengkap }}</p>
        </div>
        <div class="md:col-span-2">
            <p class="text-[11px] text-muted-foreground uppercase">Riwayat Kegiatan BPS</p>
            <p class="font-medium text-foreground">{{ original.riwayat_kegiatan_bps || '-' }}</p>
        </div>
    </div>
</template>
