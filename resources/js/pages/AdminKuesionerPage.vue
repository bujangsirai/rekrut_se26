<script setup lang="ts">
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import { Button } from '@/components/ui/button';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type QuestionType = 'radio' | 'select' | 'textarea' | 'text';

interface QuestionOption {
    label: string;
    value: string;
}

interface QuestionStructure {
    name: string;
    label: string;
    type: QuestionType;
    scoring: number;
    required?: boolean;
    placeholder?: string;
    helpText?: string;
    rows?: number;
    maxLength?: number;
    options?: QuestionOption[];
}

interface KuesionerStructure {
    title?: string;
    description?: string;
    questions?: QuestionStructure[];
}

const props = defineProps<{
    kuesionerList: Array<{
        id: number;
        status: 'active' | 'inactive';
        structure: KuesionerStructure | null;
        created_at: string | null;
    }>;
    activeStructure: KuesionerStructure | null;
}>();

defineOptions({
    layout: LayoutAdmin,
});

const page = usePage();
const errors = computed<Record<string, string>>(() => (page.props.errors ?? {}) as Record<string, string>);

const formTitle = ref(props.activeStructure?.title ?? 'Form Wawancara');
const formDescription = ref(props.activeStructure?.description ?? 'Silakan isi pertanyaan berikut.');
const formStatus = ref<'active' | 'inactive'>('inactive');
const questions = ref<QuestionStructure[]>(
    Array.isArray(props.activeStructure?.questions)
        ? props.activeStructure!.questions!.map((question) => ({ ...question }))
        : [],
);

const builderError = ref('');
const draftName = ref('');
const draftLabel = ref('');
const draftType = ref<QuestionType>('text');
const draftRequired = ref(true);
const draftPlaceholder = ref('');
const draftHelpText = ref('');
const draftRows = ref(4);
const draftMaxLength = ref<number | null>(null);
const draftScoring = ref(0);
const draftOptionsText = ref('Ya|ya\nTidak|tidak');

const showOptionsField = computed(() => draftType.value === 'radio' || draftType.value === 'select');

const structurePreview = computed(() => {
    return JSON.stringify(
        {
            title: formTitle.value.trim() || 'Form Wawancara',
            description: formDescription.value.trim(),
            questions: questions.value,
        },
        null,
        2,
    );
});

function parseOptions(rawOptions: string): QuestionOption[] {
    return rawOptions
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean)
        .map((line) => {
            const [labelRaw, valueRaw] = line.split('|');
            const label = (labelRaw ?? '').trim();
            const value = (valueRaw ?? labelRaw ?? '').trim();
            return { label, value };
        })
        .filter((option) => option.label.length > 0 && option.value.length > 0);
}

function resetDraft(): void {
    draftName.value = '';
    draftLabel.value = '';
    draftType.value = 'text';
    draftRequired.value = true;
    draftPlaceholder.value = '';
    draftHelpText.value = '';
    draftRows.value = 4;
    draftMaxLength.value = null;
    draftScoring.value = 0;
    draftOptionsText.value = 'Ya|ya\nTidak|tidak';
}

function addQuestion(): void {
    builderError.value = '';

    if (!draftName.value.trim()) {
        builderError.value = 'Field "name" wajib diisi.';
        return;
    }

    if (!draftLabel.value.trim()) {
        builderError.value = 'Field "label" wajib diisi.';
        return;
    }

    if (questions.value.some((question) => question.name === draftName.value.trim())) {
        builderError.value = 'Name sudah digunakan. Gunakan name yang unik.';
        return;
    }

    const question: QuestionStructure = {
        name: draftName.value.trim(),
        label: draftLabel.value.trim(),
        type: draftType.value,
        scoring: Number.isFinite(draftScoring.value) ? Math.max(0, draftScoring.value) : 0,
        required: draftRequired.value,
    };

    if (draftPlaceholder.value.trim()) {
        question.placeholder = draftPlaceholder.value.trim();
    }

    if (draftHelpText.value.trim()) {
        question.helpText = draftHelpText.value.trim();
    }

    if (draftMaxLength.value && draftMaxLength.value > 0) {
        question.maxLength = draftMaxLength.value;
    }

    if (draftType.value === 'textarea') {
        question.rows = draftRows.value > 0 ? draftRows.value : 4;
    }

    if (showOptionsField.value) {
        const parsedOptions = parseOptions(draftOptionsText.value);
        if (!parsedOptions.length) {
            builderError.value = 'Field options wajib diisi untuk type radio/select.';
            return;
        }
        question.options = parsedOptions;
    }

    questions.value.push(question);
    resetDraft();
}

function removeQuestion(index: number): void {
    questions.value.splice(index, 1);
}

function moveQuestion(index: number, direction: 'up' | 'down'): void {
    const targetIndex = direction === 'up' ? index - 1 : index + 1;
    if (targetIndex < 0 || targetIndex >= questions.value.length) {
        return;
    }

    const current = questions.value[index];
    questions.value[index] = questions.value[targetIndex];
    questions.value[targetIndex] = current;
}

function saveStructure(): void {
    builderError.value = '';

    if (!questions.value.length) {
        builderError.value = 'Tambahkan minimal 1 pertanyaan sebelum menyimpan.';
        return;
    }

    router.post(
        '/admin/kuesioner',
        {
            structure: structurePreview.value,
            status: formStatus.value,
        },
        {
            preserveScroll: true,
        },
    );
}

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString('id-ID');
}
</script>

<template>
    <Head title="Kelola Kuesioner" />

    <div class="mx-auto max-w-6xl space-y-5">
        <header class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-900">Kelola Kuesioner Mitra</h1>
            <p class="mt-1 text-sm text-slate-600">Buat struktur JSON kuesioner (query builder), lalu simpan dengan status aktif / tidak aktif.</p>
        </header>

        <section class="grid gap-5 xl:grid-cols-2">
            <article class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Builder Pertanyaan</h2>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="space-y-1 md:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Judul Form</span>
                        <input v-model="formTitle" type="text" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500" />
                    </label>

                    <label class="space-y-1 md:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Deskripsi Form</span>
                        <input
                            v-model="formDescription"
                            type="text"
                            class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </label>

                    <label class="space-y-1">
                        <span class="text-sm font-medium text-slate-700">Name</span>
                        <input
                            v-model="draftName"
                            type="text"
                            placeholder="contoh: pengalaman_survei"
                            class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </label>

                    <label class="space-y-1">
                        <span class="text-sm font-medium text-slate-700">Type</span>
                        <select v-model="draftType" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500">
                            <option value="text">text</option>
                            <option value="textarea">textarea</option>
                            <option value="select">select</option>
                            <option value="radio">radio</option>
                        </select>
                    </label>

                    <label class="space-y-1 md:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Label Pertanyaan</span>
                        <input
                            v-model="draftLabel"
                            type="text"
                            placeholder="Tulis pertanyaan..."
                            class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </label>

                    <label class="space-y-1 md:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Placeholder (opsional)</span>
                        <input
                            v-model="draftPlaceholder"
                            type="text"
                            placeholder="Contoh: Tulis jawaban Anda..."
                            class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </label>

                    <label class="space-y-1 md:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Help Text (opsional)</span>
                        <input
                            v-model="draftHelpText"
                            type="text"
                            placeholder="Petunjuk tambahan"
                            class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </label>

                    <label v-if="draftType === 'textarea'" class="space-y-1">
                        <span class="text-sm font-medium text-slate-700">Rows</span>
                        <input v-model.number="draftRows" type="number" min="1" class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500" />
                    </label>

                    <label class="space-y-1">
                        <span class="text-sm font-medium text-slate-700">Max Length (opsional)</span>
                        <input
                            v-model.number="draftMaxLength"
                            type="number"
                            min="1"
                            class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </label>

                    <label class="space-y-1">
                        <span class="text-sm font-medium text-slate-700">Scoring</span>
                        <input
                            v-model.number="draftScoring"
                            type="number"
                            min="0"
                            class="h-10 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                        />
                    </label>

                    <label v-if="showOptionsField" class="space-y-1 md:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Options (1 baris = `label|value`)</span>
                        <textarea
                            v-model="draftOptionsText"
                            rows="4"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500"
                        />
                    </label>

                    <label class="inline-flex items-center gap-2 md:col-span-2">
                        <input v-model="draftRequired" type="checkbox" class="h-4 w-4 accent-cyan-600" />
                        <span class="text-sm text-slate-700">Required</span>
                    </label>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Button type="button" class="cursor-pointer" @click="addQuestion">Tambah Pertanyaan</Button>
                    <Button type="button" variant="outline" class="cursor-pointer" @click="resetDraft">Reset Draft</Button>
                </div>

                <p v-if="builderError" class="text-sm font-medium text-red-600">{{ builderError }}</p>
                <p v-if="errors.structure" class="text-sm font-medium text-red-600">{{ errors.structure }}</p>

                <div class="space-y-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <h3 class="text-sm font-semibold text-slate-800">Daftar Pertanyaan ({{ questions.length }})</h3>
                    <div v-if="questions.length" class="space-y-2">
                        <div v-for="(question, index) in questions" :key="question.name" class="rounded-md border border-slate-200 bg-white p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ index + 1 }}. {{ question.label }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ question.name }} • {{ question.type }} • skor {{ question.scoring }} •
                                        {{ question.required ? 'required' : 'optional' }}
                                    </p>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-600" @click="moveQuestion(index, 'up')">↑</button>
                                    <button type="button" class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-600" @click="moveQuestion(index, 'down')">↓</button>
                                    <button type="button" class="rounded border border-red-300 px-2 py-1 text-xs text-red-600" @click="removeQuestion(index)">Hapus</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-500">Belum ada pertanyaan.</p>
                </div>

                <div class="flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <label class="space-y-1">
                        <span class="text-sm font-medium text-slate-700">Status Saat Simpan</span>
                        <select v-model="formStatus" class="h-10 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500">
                            <option value="inactive">inactive</option>
                            <option value="active">active</option>
                        </select>
                    </label>

                    <Button type="button" class="cursor-pointer" @click="saveStructure">Simpan Struktur</Button>
                </div>
            </article>

            <article class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Preview JSON</h2>
                <textarea readonly :value="structurePreview" rows="24" class="w-full rounded-md border border-slate-300 bg-slate-50 px-3 py-2 font-mono text-xs text-slate-800" />
            </article>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Riwayat Kuesioner Tersimpan</h2>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="py-2 pr-3 font-medium">ID</th>
                            <th class="py-2 pr-3 font-medium">Status</th>
                            <th class="py-2 pr-3 font-medium">Jumlah Pertanyaan</th>
                            <th class="py-2 font-medium">Disimpan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in kuesionerList" :key="item.id" class="border-b border-slate-100 last:border-0">
                            <td class="py-2 pr-3 font-semibold text-slate-900">#{{ item.id }}</td>
                            <td class="py-2 pr-3">
                                <span
                                    :class="[
                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                        item.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700',
                                    ]"
                                >
                                    {{ item.status }}
                                </span>
                            </td>
                            <td class="py-2 pr-3 text-slate-700">{{ item.structure?.questions?.length ?? 0 }}</td>
                            <td class="py-2 text-slate-700">{{ formatDate(item.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
