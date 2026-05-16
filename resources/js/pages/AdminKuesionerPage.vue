<script setup lang="ts">
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { TanStackInput } from '@/components/ui/form';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { computed, ref } from 'vue';

type QuestionType = 'radio' | 'select' | 'checkbox' | 'textarea' | 'text' | 'label';

interface QuestionOption {
    label: string;
    value: string;
}

interface ScoringOption {
    label: string;
    score: number;
}

interface QuestionStructure {
    name: string;
    label: string;
    value?: string;
    type: QuestionType;
    is_showing_respondent?: boolean;
    is_showing_assessor?: boolean;
    is_showing?: boolean;
    is_scoring?: boolean;
    is_validation?: boolean;
    scoringOptions?: ScoringOption[];
    required?: boolean;
    placeholder?: string;
    helpText?: string;
    validationRegex?: string;
    validationMessage?: string;
    options?: QuestionOption[];
}

interface BuilderQuestion {
    name: string;
    label: string;
    type: QuestionType;
    required: boolean;
    placeholder: string;
    helpText: string;
    isShowingRespondent: boolean;
    isShowingAssessor: boolean;
    isScoring: boolean;
    isValidation: boolean;
    optionsText: string;
    scoringOptionsText: string;
    validationRegex: string;
    validationMessage: string;
}

interface KuesionerStructure {
    title?: string;
    description?: string;
    questions?: QuestionStructure[];
}

interface KuesionerRow {
    id: number;
    status: 'active' | 'inactive';
    structure: KuesionerStructure | null;
    created_at: string | null;
}

const props = defineProps<{
    kuesionerList: KuesionerRow[];
}>();

defineOptions({
    layout: LayoutAdmin,
});

const page = usePage();
const errors = computed<Record<string, string>>(() => (page.props.errors ?? {}) as Record<string, string>);

const isFormModalOpen = ref(false);
const modalMode = ref<'create' | 'edit'>('create');
const editingId = ref<number | null>(null);
const builderError = ref('');

const kuesionerForm = useForm({
    defaultValues: {
        title: '',
        description: '',
    },
    onSubmit: async () => Promise.resolve(),
});

const formValues = kuesionerForm.useStore((state) => state.values);
const questions = ref<BuilderQuestion[]>([]);

const tableRows = computed(() => {
    return props.kuesionerList.map((item) => ({
        id: item.id,
        title: item.structure?.title ?? '-',
        description: item.structure?.description ?? '-',
        questionsCount: item.structure?.questions?.length ?? 0,
        status: item.status,
        raw: item,
    }));
});

function createEmptyQuestion(index: number): BuilderQuestion {
    return {
        name: `pertanyaan_${index}`,
        label: '',
        type: 'text',
        required: true,
        placeholder: '',
        helpText: '',
        isShowingRespondent: true,
        isShowingAssessor: true,
        isScoring: false,
        isValidation: false,
        optionsText: 'Ya|ya\nTidak|tidak',
        scoringOptionsText: 'Best answer|10\nGood answer|8\nBad answer|3',
        validationRegex: '',
        validationMessage: '',
    };
}

function formatOptionsText(options?: QuestionOption[]): string {
    if (!options?.length) {
        return 'Ya|ya\nTidak|tidak';
    }

    return options.map((option) => `${option.label}|${option.value}`).join('\n');
}

function formatScoringOptionsText(options?: ScoringOption[]): string {
    if (!options?.length) {
        return 'Best answer|10\nGood answer|8\nBad answer|3';
    }

    return options.map((option) => `${option.label}|${option.score}`).join('\n');
}

function toBuilderQuestion(question: QuestionStructure, index: number): BuilderQuestion {
    const hasScoringOptions = Array.isArray(question.scoringOptions) && question.scoringOptions.length > 0;
    const hasLegacyScoring = typeof (question as { scoring?: unknown }).scoring === 'number';
    const hasLegacyValidationRegex = typeof question.validationRegex === 'string' && question.validationRegex.trim() !== '';

    const legacyShowingRespondent = question.is_showing ?? true;

    return {
        name: question.name || `pertanyaan_${index + 1}`,
        label: question.label || question.value || '',
        type: question.type || 'text',
        required: question.required ?? true,
        placeholder: question.placeholder ?? '',
        helpText: question.helpText ?? '',
        isShowingRespondent: question.is_showing_respondent ?? legacyShowingRespondent,
        isShowingAssessor: question.is_showing_assessor ?? true,
        isScoring: question.is_scoring ?? (hasScoringOptions || hasLegacyScoring),
        isValidation: question.is_validation ?? hasLegacyValidationRegex,
        optionsText: formatOptionsText(question.options),
        scoringOptionsText: formatScoringOptionsText(question.scoringOptions),
        validationRegex: question.validationRegex ?? '',
        validationMessage: question.validationMessage ?? '',
    };
}

function resetBuilder(): void {
    builderError.value = '';
    editingId.value = null;
    questions.value = [];
    kuesionerForm.reset();
}

function openCreateModal(): void {
    resetBuilder();
    modalMode.value = 'create';
    isFormModalOpen.value = true;
}

function openEditModal(item: KuesionerRow): void {
    resetBuilder();
    modalMode.value = 'edit';
    editingId.value = item.id;
    kuesionerForm.setFieldValue('title', item.structure?.title ?? '');
    kuesionerForm.setFieldValue('description', item.structure?.description ?? '');
    questions.value = Array.isArray(item.structure?.questions) ? item.structure!.questions!.map((question, index) => toBuilderQuestion(question, index)) : [];
    isFormModalOpen.value = true;
}

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

function parseScoringOptions(rawOptions: string): ScoringOption[] {
    return rawOptions
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean)
        .map((line) => {
            const [labelRaw, scoreRaw] = line.split('|');
            const label = (labelRaw ?? '').trim();
            const scoreToken = (scoreRaw ?? '').trim();
            const score = /^-?\d+$/.test(scoreToken) ? Number(scoreToken) : Number.NaN;

            return {
                label,
                score,
            };
        })
        .filter((option) => option.label.length > 0 && Number.isInteger(option.score));
}

function addQuestion(): void {
    questions.value.push(createEmptyQuestion(questions.value.length + 1));
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

function buildStructure(validate = false): KuesionerStructure | null {
    const title = formValues.value.title.trim();
    const description = formValues.value.description.trim();

    if (validate) {
        if (!title) {
            builderError.value = 'Judul form wajib diisi.';
            return null;
        }

        if (!description) {
            builderError.value = 'Deskripsi form wajib diisi.';
            return null;
        }
    }

    const normalizedQuestions: QuestionStructure[] = [];
    const usedNames = new Set<string>();

    for (let index = 0; index < questions.value.length; index += 1) {
        const source = questions.value[index];
        const name = source.name.trim();
        const label = source.label.trim();

        if (validate) {
            if (!name) {
                builderError.value = `Name pertanyaan ke-${index + 1} wajib diisi.`;
                return null;
            }

            if (!label) {
                builderError.value = `Label pertanyaan ke-${index + 1} wajib diisi.`;
                return null;
            }

            if (usedNames.has(name)) {
                builderError.value = `Name "${name}" duplikat. Gunakan name yang unik.`;
                return null;
            }
        }

        usedNames.add(name);

        const question: QuestionStructure = {
            name,
            label,
            type: source.type,
            required: source.required,
            is_showing_respondent: source.isShowingRespondent,
            is_showing_assessor: source.isShowingAssessor,
            is_scoring: source.isScoring,
            is_validation: source.type === 'label' ? false : source.isValidation,
        };

        if (source.placeholder.trim()) {
            question.placeholder = source.placeholder.trim();
        }

        if (source.helpText.trim()) {
            question.helpText = source.helpText.trim();
        }

        if (source.type === 'radio' || source.type === 'select' || source.type === 'checkbox') {
            const parsedOptions = parseOptions(source.optionsText);
            if (validate && !parsedOptions.length) {
                builderError.value = `Options pertanyaan ke-${index + 1} wajib diisi.`;
                return null;
            }

            if (parsedOptions.length) {
                question.options = parsedOptions;
            }
        }

        if (question.is_scoring) {
            const parsedScoringOptions = parseScoringOptions(source.scoringOptionsText);
            if (validate && !parsedScoringOptions.length) {
                builderError.value = `Opsi scoring pertanyaan ke-${index + 1} wajib diisi (format: label|skor).`;
                return null;
            }

            if (parsedScoringOptions.length) {
                question.scoringOptions = parsedScoringOptions;
            }
        }

        if (question.is_validation) {
            const regexPattern = source.validationRegex.trim();
            if (validate && regexPattern === '') {
                builderError.value = `RegExp pertanyaan ke-${index + 1} wajib diisi saat validasi aktif.`;
                return null;
            }

            if (regexPattern === '') {
                normalizedQuestions.push(question);
                continue;
            }

            try {
                new RegExp(regexPattern);
            } catch {
                builderError.value = `RegExp pertanyaan ke-${index + 1} tidak valid.`;
                return null;
            }

            question.validationRegex = regexPattern;
            if (source.validationMessage.trim() !== '') {
                question.validationMessage = source.validationMessage.trim();
            }
        }

        normalizedQuestions.push(question);
    }

    return {
        title,
        description,
        questions: normalizedQuestions,
    };
}

function saveStructure(): void {
    builderError.value = '';

    const structure = buildStructure(true);
    if (!structure) {
        return;
    }

    if (modalMode.value === 'create') {
        router.post(
            '/admin/kuesioner',
            {
                structure: JSON.stringify(structure),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    isFormModalOpen.value = false;
                    resetBuilder();
                },
            },
        );
        return;
    }

    if (editingId.value === null) {
        builderError.value = 'Kuesioner yang akan diubah tidak ditemukan.';
        return;
    }

    router.put(
        `/admin/kuesioner/${editingId.value}`,
        {
            structure: JSON.stringify(structure),
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isFormModalOpen.value = false;
                resetBuilder();
            },
        },
    );
}
</script>

<template>
    <Head title="Kelola Kuesioner" />

    <div class="mx-auto max-w-6xl space-y-5">
        <header class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Kelola Kuesioner Mitra</h1>
                    <p class="mt-1 text-sm text-slate-600">Tambah kuesioner baru dan ubah pertanyaan lewat tombol Modified Form.</p>
                </div>
                <Button type="button" class="cursor-pointer" @click="openCreateModal">Tambah Kuesioner</Button>
            </div>
        </header>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Riwayat Kuesioner Tersimpan</h2>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="py-2 pr-3 font-medium">Judul</th>
                            <th class="py-2 pr-3 font-medium">Deskripsi</th>
                            <th class="py-2 pr-3 font-medium">Jumlah Pertanyaan</th>
                            <th class="py-2 pr-3 font-medium">Status</th>
                            <th class="py-2 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in tableRows" :key="item.id" class="border-b border-slate-100 last:border-0">
                            <td class="py-2 pr-3 font-semibold text-slate-900">{{ item.title }}</td>
                            <td class="py-2 pr-3 text-slate-700">{{ item.description }}</td>
                            <td class="py-2 pr-3 text-slate-700">{{ item.questionsCount }}</td>
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
                            <td class="py-2">
                                <a
                                    :href="`/admin/kuesioner/${item.id}/edit`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex h-9 items-center rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-900 shadow-xs transition hover:bg-slate-100"
                                >
                                    Modified Form
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <Dialog :open="isFormModalOpen" @update:open="(val) => (isFormModalOpen = val)">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-5xl">
            <DialogHeader>
                <DialogTitle>{{ modalMode === 'create' ? 'Tambah Kuesioner' : 'Modified Form' }}</DialogTitle>
                <DialogDescription>
                    {{ modalMode === 'create' ? 'Isi judul dan deskripsi, lalu simpan.' : 'Ubah pertanyaan di sini lalu simpan perubahan.' }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <div class="grid gap-4 md:grid-cols-2">
                    <TanStackInput :form="kuesionerForm" name="title" label="Judul Form*" />
                    <TanStackInput :form="kuesionerForm" name="description" label="Deskripsi Form*" />
                </div>

                <div v-if="modalMode === 'edit'" class="space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-slate-800">Pertanyaan ({{ questions.length }})</h3>
                        <Button type="button" class="cursor-pointer" @click="addQuestion">Tambah Pertanyaan</Button>
                    </div>

                    <div v-if="questions.length" class="space-y-3">
                        <div v-for="(question, index) in questions" :key="`${question.name}-${index}`" class="space-y-3 rounded-md border border-slate-200 bg-white p-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-slate-900">Pertanyaan {{ index + 1 }}</p>
                                <div class="flex gap-1">
                                    <button type="button" class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-600" @click="moveQuestion(index, 'up')">↑</button>
                                    <button type="button" class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-600" @click="moveQuestion(index, 'down')">↓</button>
                                    <button type="button" class="rounded border border-red-300 px-2 py-1 text-xs text-red-600" @click="removeQuestion(index)">Hapus</button>
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2">
                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">Name</span>
                                    <input
                                        v-model="question.name"
                                        type="text"
                                        spellcheck="false"
                                        autocorrect="off"
                                        autocapitalize="off"
                                        class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                                    />
                                </label>

                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">Type</span>
                                    <select v-model="question.type" class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500">
                                        <option value="text">text</option>
                                        <option value="textarea">textarea</option>
                                        <option value="select">select</option>
                                        <option value="radio">radio</option>
                                        <option value="checkbox">checkbox</option>
                                        <option value="label">label</option>
                                    </select>
                                </label>

                                <label class="space-y-1 md:col-span-2">
                                    <span class="text-xs font-medium text-slate-700">
                                        {{ question.type === 'label' ? 'Konten Label (HTML)' : 'Label Pertanyaan' }}
                                    </span>
                                    <textarea
                                        v-if="question.type === 'label'"
                                        v-model="question.label"
                                        rows="3"
                                        spellcheck="false"
                                        autocorrect="off"
                                        autocapitalize="off"
                                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500"
                                    />
                                    <input
                                        v-else
                                        v-model="question.label"
                                        type="text"
                                        spellcheck="false"
                                        autocorrect="off"
                                        autocapitalize="off"
                                        class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                                    />
                                </label>

                                <label v-if="question.type !== 'label'" class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">Placeholder (opsional)</span>
                                    <input
                                        v-model="question.placeholder"
                                        type="text"
                                        spellcheck="false"
                                        autocorrect="off"
                                        autocapitalize="off"
                                        class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                                    />
                                </label>

                                <label v-if="question.type !== 'label'" class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">Help Text (opsional)</span>
                                    <input
                                        v-model="question.helpText"
                                        type="text"
                                        spellcheck="false"
                                        autocorrect="off"
                                        autocapitalize="off"
                                        class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                                    />
                                </label>

                                <label class="inline-flex items-center gap-2 md:col-span-2">
                                    <input v-model="question.isShowingRespondent" type="checkbox" class="h-4 w-4 accent-cyan-600" />
                                    <span class="text-sm text-slate-700">Tampilkan ke responden</span>
                                </label>

                                <label class="inline-flex items-center gap-2 md:col-span-2">
                                    <input v-model="question.isShowingAssessor" type="checkbox" class="h-4 w-4 accent-cyan-600" />
                                    <span class="text-sm text-slate-700">Tampilkan ke assessor</span>
                                </label>

                                <label class="inline-flex items-center gap-2 md:col-span-2">
                                    <input v-model="question.isScoring" type="checkbox" class="h-4 w-4 accent-cyan-600" />
                                    <span class="text-sm text-slate-700">Gunakan scoring (opsi skor integer)</span>
                                </label>

                                <label v-if="question.isScoring" class="space-y-1 md:col-span-2">
                                    <span class="text-xs font-medium text-slate-700">Opsi Scoring (1 baris = `label|skor`)</span>
                                    <textarea
                                        v-model="question.scoringOptionsText"
                                        rows="3"
                                        spellcheck="false"
                                        autocorrect="off"
                                        autocapitalize="off"
                                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500"
                                    />
                                </label>

                                <label v-if="question.type !== 'label'" class="inline-flex items-center gap-2 md:col-span-2">
                                    <input v-model="question.isValidation" type="checkbox" class="h-4 w-4 accent-cyan-600" />
                                    <span class="text-sm text-slate-700">Gunakan validasi RegExp</span>
                                </label>

                                <label v-if="question.type !== 'label' && question.isValidation" class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">RegExp Validasi (opsional)</span>
                                    <input
                                        v-model="question.validationRegex"
                                        type="text"
                                        spellcheck="false"
                                        autocorrect="off"
                                        autocapitalize="off"
                                        class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                                        placeholder="contoh: ^[0-9]{16}$"
                                    />
                                </label>

                                <label v-if="question.type !== 'label' && question.isValidation" class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">Pesan Error Validasi (opsional)</span>
                                    <input
                                        v-model="question.validationMessage"
                                        type="text"
                                        spellcheck="false"
                                        autocorrect="off"
                                        autocapitalize="off"
                                        class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                                        placeholder="Input tidak sesuai format."
                                    />
                                </label>

                                <label v-if="question.type === 'radio' || question.type === 'select' || question.type === 'checkbox'" class="space-y-1 md:col-span-2">
                                    <span class="text-xs font-medium text-slate-700">Options (1 baris = `label|value`)</span>
                                    <textarea
                                        v-model="question.optionsText"
                                        rows="3"
                                        spellcheck="false"
                                        autocorrect="off"
                                        autocapitalize="off"
                                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500"
                                    />
                                </label>

                                <label v-if="question.type !== 'label'" class="inline-flex items-center gap-2 md:col-span-2">
                                    <input v-model="question.required" type="checkbox" class="h-4 w-4 accent-cyan-600" />
                                    <span class="text-sm text-slate-700">Required</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-500">Belum ada pertanyaan. Klik "Tambah Pertanyaan" untuk mulai.</p>
                </div>

                <p v-if="builderError" class="text-sm font-medium text-red-600">{{ builderError }}</p>
                <p v-if="errors.structure" class="text-sm font-medium text-red-600">{{ errors.structure }}</p>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" class="cursor-pointer" @click="isFormModalOpen = false">Batal</Button>
                <Button type="button" class="cursor-pointer" @click="saveStructure">
                    {{ modalMode === 'create' ? 'Simpan Struktur' : 'Simpan Perubahan' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
