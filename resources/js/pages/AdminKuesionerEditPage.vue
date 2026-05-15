<script setup lang="ts">
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import { Button } from '@/components/ui/button';
import { TanStackInput } from '@/components/ui/form';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useForm } from '@tanstack/vue-form';
import { computed, ref } from 'vue';

type QuestionType = 'radio' | 'select' | 'textarea' | 'text' | 'label';
type ScoringMode = 'fixed' | 'options';

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
    type: QuestionType;
    scoring: number;
    scoringOptions?: ScoringOption[];
    required?: boolean;
    placeholder?: string;
    helpText?: string;
    rows?: number;
    maxLength?: number;
    options?: QuestionOption[];
}

interface BuilderQuestion {
    name: string;
    label: string;
    type: QuestionType;
    required: boolean;
    placeholder: string;
    helpText: string;
    rows: number;
    maxLength: number | null;
    scoring: number;
    scoringMode: ScoringMode;
    optionsText: string;
    scoringOptionsText: string;
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
    kuesioner: KuesionerRow;
}>();

defineOptions({
    layout: LayoutAdmin,
});

const page = usePage();
const errors = computed<Record<string, string>>(() => (page.props.errors ?? {}) as Record<string, string>);
const builderError = ref('');

const kuesionerForm = useForm({
    defaultValues: {
        title: props.kuesioner.structure?.title ?? '',
        description: props.kuesioner.structure?.description ?? '',
    },
    onSubmit: async () => Promise.resolve(),
});

const formValues = kuesionerForm.useStore((state) => state.values);
const questions = ref<BuilderQuestion[]>(
    Array.isArray(props.kuesioner.structure?.questions)
        ? props.kuesioner.structure!.questions!.map((question, index) => toBuilderQuestion(question, index))
        : [],
);

function createEmptyQuestion(index: number): BuilderQuestion {
    return {
        name: `pertanyaan_${index}`,
        label: '',
        type: 'text',
        required: true,
        placeholder: '',
        helpText: '',
        rows: 4,
        maxLength: null,
        scoring: 0,
        scoringMode: 'fixed',
        optionsText: 'Ya|ya\nTidak|tidak',
        scoringOptionsText: 'Best answer|10\nGood answer|8\nBad answer|3',
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

    return {
        name: question.name || `pertanyaan_${index + 1}`,
        label: question.label || '',
        type: question.type || 'text',
        required: question.required ?? true,
        placeholder: question.placeholder ?? '',
        helpText: question.helpText ?? '',
        rows: question.rows ?? 4,
        maxLength: question.maxLength ?? null,
        scoring: Number.isFinite(question.scoring) ? question.scoring : 0,
        scoringMode: hasScoringOptions ? 'options' : 'fixed',
        optionsText: formatOptionsText(question.options),
        scoringOptionsText: formatScoringOptionsText(question.scoringOptions),
    };
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
            const score = Number.parseInt((scoreRaw ?? '').trim(), 10);

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
            scoring: Number.isFinite(source.scoring) ? Math.max(0, source.scoring) : 0,
        };

        if (source.placeholder.trim()) {
            question.placeholder = source.placeholder.trim();
        }

        if (source.helpText.trim()) {
            question.helpText = source.helpText.trim();
        }

        if (source.maxLength && source.maxLength > 0) {
            question.maxLength = source.maxLength;
        }

        if (source.type === 'textarea') {
            question.rows = source.rows > 0 ? source.rows : 4;
        }

        if (source.type === 'radio' || source.type === 'select') {
            const parsedOptions = parseOptions(source.optionsText);
            if (validate && !parsedOptions.length) {
                builderError.value = `Options pertanyaan ke-${index + 1} wajib diisi.`;
                return null;
            }

            if (parsedOptions.length) {
                question.options = parsedOptions;
            }
        }

        if (source.scoringMode === 'options') {
            const parsedScoringOptions = parseScoringOptions(source.scoringOptionsText);
            if (validate && !parsedScoringOptions.length) {
                builderError.value = `Opsi scoring pertanyaan ke-${index + 1} wajib diisi (format: label|skor).`;
                return null;
            }

            if (parsedScoringOptions.length) {
                question.scoringOptions = parsedScoringOptions;
                question.scoring = Math.max(...parsedScoringOptions.map((option) => option.score));
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

    router.put(
        `/admin/kuesioner/${props.kuesioner.id}`,
        {
            structure: JSON.stringify(structure),
        },
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <Head title="Modified Form Kuesioner" />

    <div class="mx-auto max-w-6xl space-y-5">
        <header class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Modified Form</h1>
                    <p class="mt-1 text-sm text-slate-600">Ubah judul, deskripsi, dan pertanyaan lalu simpan perubahan.</p>
                </div>
                <Link href="/admin/kuesioner">
                    <Button type="button" variant="outline" class="cursor-pointer">Kembali</Button>
                </Link>
            </div>
        </header>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="space-y-4 py-2">
                <div class="grid gap-4 md:grid-cols-2">
                    <TanStackInput :form="kuesionerForm" name="title" label="Judul Form*" />
                    <TanStackInput :form="kuesionerForm" name="description" label="Deskripsi Form*" />
                </div>

                <div class="space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
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
                                    <input v-model="question.name" type="text" class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500" />
                                </label>

                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">Type</span>
                                    <select v-model="question.type" class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500">
                                        <option value="text">text</option>
                                        <option value="textarea">textarea</option>
                                        <option value="select">select</option>
                                        <option value="radio">radio</option>
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
                                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500"
                                    />
                                    <input
                                        v-else
                                        v-model="question.label"
                                        type="text"
                                        class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500"
                                    />
                                </label>

                                <label class="space-y-1 md:col-span-2">
                                    <span class="text-xs font-medium text-slate-700">Placeholder (opsional)</span>
                                    <input v-model="question.placeholder" type="text" class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500" />
                                </label>

                                <label class="space-y-1 md:col-span-2">
                                    <span class="text-xs font-medium text-slate-700">Help Text (opsional)</span>
                                    <input v-model="question.helpText" type="text" class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500" />
                                </label>

                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">Max Length (opsional)</span>
                                    <input v-model.number="question.maxLength" type="number" min="1" class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500" />
                                </label>

                                <label class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">Mode Scoring</span>
                                    <select v-model="question.scoringMode" class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500">
                                        <option value="fixed">Skor tunggal</option>
                                        <option value="options">Opsi skor</option>
                                    </select>
                                </label>

                                <label v-if="question.scoringMode === 'fixed'" class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">Scoring (Integer)</span>
                                    <input v-model.number="question.scoring" type="number" min="0" class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500" />
                                </label>

                                <label v-else class="space-y-1 md:col-span-2">
                                    <span class="text-xs font-medium text-slate-700">Opsi Scoring (1 baris = `label|skor`)</span>
                                    <textarea
                                        v-model="question.scoringOptionsText"
                                        rows="3"
                                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500"
                                    />
                                </label>

                                <label v-if="question.type === 'textarea'" class="space-y-1">
                                    <span class="text-xs font-medium text-slate-700">Rows</span>
                                    <input v-model.number="question.rows" type="number" min="1" class="h-9 w-full rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-cyan-500" />
                                </label>

                                <label v-if="question.type === 'radio' || question.type === 'select'" class="space-y-1 md:col-span-2">
                                    <span class="text-xs font-medium text-slate-700">Options (1 baris = `label|value`)</span>
                                    <textarea v-model="question.optionsText" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-cyan-500" />
                                </label>

                                <label class="inline-flex items-center gap-2 md:col-span-2">
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

            <div class="mt-4 flex justify-end">
                <Button type="button" class="cursor-pointer" @click="saveStructure">Simpan Perubahan</Button>
            </div>
        </section>
    </div>
</template>
