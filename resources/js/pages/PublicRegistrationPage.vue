<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Check, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

defineOptions({
    // @ts-ignore
    layout: null,
});

const props = defineProps<{
    kecamatanOptions: Array<{
        kode_kec: string;
        nama_kec: string;
    }>;
    desaOptions: Array<{
        kode_kec: string;
        kode_desa: string;
        nama_desa: string;
    }>;
}>();

const firstKecamatanCode = props.kecamatanOptions[0]?.kode_kec ?? '';

const form = useForm({
    nama_lengkap: '',
    email: '',
    jenis_kelamin: 'Laki-laki',
    kode_kec: firstKecamatanCode,
    kode_desa: '',
    alamat_lengkap: '',
    tanggal_lahir: '',
    tempat_lahir: '',
    status_perkawinan: 'Belum Kawin',
    pendidikan_terakhir: 'SLTP/Kebawah',
    pekerjaan: '',
    nomor_whatsapp: '',
    riwayat_kegiatan_bps: '',
    ktp_file: null as File | null,
});

const kecamatanOpen = ref(false);
const desaOpen = ref(false);

function submit(): void {
    form.post('/daftar', {
        preserveScroll: true,
        forceFormData: true,
    });
}

function handleKtpFileChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    form.ktp_file = target.files?.[0] ?? null;
}

const filteredDesaOptions = computed(() => {
    if (!form.kode_kec) {
        return [];
    }

    return props.desaOptions.filter((item) => item.kode_desa.slice(0, 7) === form.kode_kec);
});

const selectedKecamatanLabel = computed(() => {
    return props.kecamatanOptions.find((item) => item.kode_kec === form.kode_kec)?.nama_kec ?? '';
});

const selectedDesaLabel = computed(() => {
    return filteredDesaOptions.value.find((item) => item.kode_desa === form.kode_desa)?.nama_desa ?? '';
});

watch(
    () => form.kode_kec,
    (kodeKecamatan) => {
        if (!kodeKecamatan) {
            form.kode_desa = '';

            return;
        }

        const firstDesa = props.desaOptions.find((item) => item.kode_desa.slice(0, 7) === kodeKecamatan);
        form.kode_desa = firstDesa?.kode_desa ?? '';
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Form Pendaftaran Mitra" />

    <div class="min-h-screen bg-linear-to-b from-cyan-50 via-slate-100 to-slate-200 px-2 py-3 sm:px-6 sm:py-6">
        <div class="mx-auto max-w-5xl space-y-4">
            <header class="rounded-xl border border-cyan-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h1 class="text-lg font-bold text-cyan-800 sm:text-xl">Form Pendaftaran Mitra SE2026</h1>
                    <Link href="/" class="text-sm font-medium text-cyan-700 hover:underline">Kembali</Link>
                </div>
            </header>

            <form class="space-y-4 sm:space-y-5" @submit.prevent="submit">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-4 py-3">
                        <h2 class="text-base font-bold text-white sm:text-lg">I. INFORMASI UMUM</h2>
                    </header>

                    <div class="p-4 sm:p-6">
                    <div class="grid gap-4 md:grid-cols-[260px_minmax(0,1fr)] md:items-center">
                        <label class="text-sm font-semibold text-slate-900">1. Nama Lengkap*</label>
                        <div>
                            <input v-model="form.nama_lengkap" type="text" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm" />
                            <p v-if="form.errors.nama_lengkap" class="mt-1 text-xs text-red-600">{{ form.errors.nama_lengkap }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">2. Alamat Email*</label>
                        <div>
                            <input v-model="form.email" type="email" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">3. Jenis Kelamin*</label>
                        <div>
                            <select v-model="form.jenis_kelamin" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                            <p v-if="form.errors.jenis_kelamin" class="mt-1 text-xs text-red-600">{{ form.errors.jenis_kelamin }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">4. Asal Kecamatan*</label>
                        <div>
                            <Popover v-model:open="kecamatanOpen">
                                <PopoverTrigger as-child>
                                    <Button
                                        variant="outline"
                                        role="combobox"
                                        :aria-expanded="kecamatanOpen"
                                        class="h-10 w-full justify-between font-normal"
                                    >
                                        <span class="truncate text-left">
                                            {{ selectedKecamatanLabel || 'Cari kecamatan...' }}
                                        </span>
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-(--reka-popper-anchor-width) p-0">
                                    <Command>
                                        <CommandInput placeholder="Cari kecamatan..." class="h-9" />
                                        <CommandEmpty>Kecamatan tidak ditemukan.</CommandEmpty>
                                        <CommandList class="group/list max-h-56">
                                            <CommandGroup class="p-1">
                                                <CommandItem
                                                    v-for="item in kecamatanOptions"
                                                    :key="item.kode_kec"
                                                    :value="`${item.kode_kec} ${item.nama_kec}`"
                                                    class="cursor-pointer rounded-md transition-colors hover:!bg-primary/10 hover:!text-primary data-[highlighted]:bg-primary/10 data-[highlighted]:text-primary group-hover/list:data-[highlighted]:bg-transparent group-hover/list:data-[highlighted]:text-foreground"
                                                    @select="
                                                        () => {
                                                            form.kode_kec = item.kode_kec;
                                                            kecamatanOpen = false;
                                                        }
                                                    "
                                                >
                                                    <span>{{ item.nama_kec }}</span>
                                                    <Check
                                                        class="ml-auto h-4 w-4"
                                                        :class="form.kode_kec === item.kode_kec ? 'opacity-100' : 'opacity-0'"
                                                    />
                                                </CommandItem>
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                            <p v-if="form.errors.kode_kec" class="mt-1 text-xs text-red-600">{{ form.errors.kode_kec }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">5. Asal Desa*</label>
                        <div>
                            <Popover v-model:open="desaOpen">
                                <PopoverTrigger as-child>
                                    <Button
                                        variant="outline"
                                        role="combobox"
                                        :aria-expanded="desaOpen"
                                        class="h-10 w-full justify-between font-normal"
                                        :disabled="!form.kode_kec"
                                    >
                                        <span class="truncate text-left">
                                            {{ selectedDesaLabel || 'Cari desa...' }}
                                        </span>
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-(--reka-popper-anchor-width) p-0">
                                    <Command>
                                        <CommandInput placeholder="Cari desa..." class="h-9" />
                                        <CommandEmpty>Desa tidak ditemukan.</CommandEmpty>
                                        <CommandList class="group/list max-h-56">
                                            <CommandGroup class="p-1">
                                                <CommandItem
                                                    v-for="item in filteredDesaOptions"
                                                    :key="item.kode_desa"
                                                    :value="`${item.kode_desa} ${item.nama_desa}`"
                                                    class="cursor-pointer rounded-md transition-colors hover:!bg-primary/10 hover:!text-primary data-[highlighted]:bg-primary/10 data-[highlighted]:text-primary group-hover/list:data-[highlighted]:bg-transparent group-hover/list:data-[highlighted]:text-foreground"
                                                    @select="
                                                        () => {
                                                            form.kode_desa = item.kode_desa;
                                                            desaOpen = false;
                                                        }
                                                    "
                                                >
                                                    <span>{{ item.nama_desa }}</span>
                                                    <Check
                                                        class="ml-auto h-4 w-4"
                                                        :class="form.kode_desa === item.kode_desa ? 'opacity-100' : 'opacity-0'"
                                                    />
                                                </CommandItem>
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                            <p v-if="form.errors.kode_desa" class="mt-1 text-xs text-red-600">{{ form.errors.kode_desa }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">6. Alamat Lengkap*</label>
                        <div>
                            <textarea v-model="form.alamat_lengkap" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
                            <p v-if="form.errors.alamat_lengkap" class="mt-1 text-xs text-red-600">{{ form.errors.alamat_lengkap }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">7. Tanggal Lahir*</label>
                        <div>
                            <input v-model="form.tanggal_lahir" type="date" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm" />
                            <p v-if="form.errors.tanggal_lahir" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal_lahir }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">8. Tempat Lahir*</label>
                        <div>
                            <input v-model="form.tempat_lahir" type="text" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm" />
                            <p v-if="form.errors.tempat_lahir" class="mt-1 text-xs text-red-600">{{ form.errors.tempat_lahir }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">9. Status Perkawinan*</label>
                        <div>
                            <select v-model="form.status_perkawinan" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm">
                                <option value="Belum Kawin">Belum Kawin</option>
                                <option value="Kawin">Kawin</option>
                                <option value="Cerai Hidup">Cerai Hidup</option>
                                <option value="Cerai Mati">Cerai Mati</option>
                            </select>
                            <p v-if="form.errors.status_perkawinan" class="mt-1 text-xs text-red-600">{{ form.errors.status_perkawinan }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">10. Pendidikan Terakhir*</label>
                        <div>
                            <select v-model="form.pendidikan_terakhir" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm">
                                <option value="SLTP/Kebawah">SLTP/Kebawah</option>
                                <option value="SLTA">SLTA</option>
                                <option value="DI/DII/DII">DI/DII/DII</option>
                                <option value="DIV/S1/S2/S3">DIV/S1/S2/S3</option>
                            </select>
                            <p v-if="form.errors.pendidikan_terakhir" class="mt-1 text-xs text-red-600">{{ form.errors.pendidikan_terakhir }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">11. Pekerjaan*</label>
                        <div>
                            <input v-model="form.pekerjaan" type="text" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm" />
                            <p v-if="form.errors.pekerjaan" class="mt-1 text-xs text-red-600">{{ form.errors.pekerjaan }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">12. Nomor WhatsApp*</label>
                        <div>
                            <input v-model="form.nomor_whatsapp" type="text" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm" />
                            <p v-if="form.errors.nomor_whatsapp" class="mt-1 text-xs text-red-600">{{ form.errors.nomor_whatsapp }}</p>
                        </div>

                        <label class="text-sm font-semibold text-slate-900">13. Riwayat Kegiatan BPS</label>
                        <div>
                            <textarea
                                v-model="form.riwayat_kegiatan_bps"
                                rows="3"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                            />
                            <p v-if="form.errors.riwayat_kegiatan_bps" class="mt-1 text-xs text-red-600">{{ form.errors.riwayat_kegiatan_bps }}</p>
                        </div>
                    </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-4 py-3">
                        <h2 class="text-base font-bold text-white sm:text-lg">II. DOKUMEN UNGGAHAN</h2>
                    </header>
                    <div class="grid gap-4 p-4 sm:p-6 md:grid-cols-[260px_minmax(0,1fr)] md:items-center">
                        <label class="text-sm font-semibold text-slate-900">15. Upload KTP*</label>
                        <div>
                            <input
                                type="file"
                                accept=".pdf,image/png,image/jpeg,image/jpg,image/webp"
                                class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-cyan-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-cyan-700"
                                @change="handleKtpFileChange"
                            />
                            <p class="mt-1 text-xs text-slate-500">Format: PDF/JPG/JPEG/PNG/WEBP. Maksimal 5MB.</p>
                            <p v-if="form.errors.ktp_file" class="mt-1 text-xs text-red-600">{{ form.errors.ktp_file }}</p>
                        </div>
                    </div>
                </section>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-green-600 px-5 text-sm font-semibold text-white transition hover:bg-green-700 disabled:opacity-60"
                    >
                        {{ form.processing ? 'Mengirim...' : 'Kirim Pendaftaran' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
