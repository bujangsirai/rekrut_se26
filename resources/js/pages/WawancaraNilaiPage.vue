<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

interface ScoringOption {
    label: string;
    score: number;
}

interface QuestionOption {
    label: string;
    value: string;
}

interface QuestionConfig {
    name: string;
    label: string;
    value?: string;
    type: 'radio' | 'select' | 'checkbox' | 'textarea' | 'text' | 'label';
    is_showing?: boolean;
    is_scoring?: boolean;
    required?: boolean;
    placeholder?: string;
    helpText?: string;
    validationRegex?: string;
    validationMessage?: string;
    options?: QuestionOption[];
    scoringOptions?: ScoringOption[];
}

function getLabelHtml(question: QuestionConfig): string {
    return question.label || question.value || '';
}

interface SelectionFormConfig {
    title: string;
    description?: string;
    questions: QuestionConfig[];
}

const props = defineProps<{
    mitra: {
        nik: string;
        nama_lengkap: string;
        kode_akses: string;
        jawaban_kuesioner?: Record<string, unknown> | null;
        penilaian_kuesioner?: Record<string, unknown> | null;
        [key: string]: unknown;
    };
    formConfig: SelectionFormConfig;
}>();

defineOptions({
    // @ts-ignore
    layout: null,
});

const assessorScores = reactive<Record<string, number | null>>({});
const assessorNotes = reactive<Record<string, string>>({});
const isSubmittingScore = ref(false);

for (const question of props.formConfig.questions ?? []) {
    const existingScoreRaw = props.mitra.penilaian_kuesioner?.[question.name];
    const existingScoreFromObject =
        existingScoreRaw && typeof existingScoreRaw === 'object' && 'score' in existingScoreRaw
            ? Number((existingScoreRaw as { score?: unknown }).score)
            : NaN;
    const existingNoteFromObject =
        existingScoreRaw && typeof existingScoreRaw === 'object' && 'note' in existingScoreRaw
            ? String((existingScoreRaw as { note?: unknown }).note ?? '')
            : '';

    assessorScores[question.name] = Number.isFinite(existingScoreFromObject) ? existingScoreFromObject : null;
    assessorNotes[question.name] = existingNoteFromObject;
}

const questionNumbers = computed(() => {
    const numbers: Record<string, number> = {};
    let current = 0;

    for (const question of props.formConfig.questions ?? []) {
        if (question.type !== 'label') {
            current += 1;
            numbers[question.name] = current;
        }
    }

    return numbers;
});

function getStoredValue(question: QuestionConfig): string | string[] {
    const savedAnswers = props.mitra.jawaban_kuesioner;
    if (!savedAnswers || typeof savedAnswers !== 'object') {
        return question.type === 'checkbox' ? [] : '';
    }

    const item = (savedAnswers as Record<string, unknown>)[question.name];
    if (typeof item === 'string') {
        return question.type === 'checkbox' ? [item] : item;
    }

    if (Array.isArray(item)) {
        return item.filter((value): value is string => typeof value === 'string');
    }

    if (item && typeof item === 'object' && 'value' in item) {
        const value = (item as { value?: unknown }).value;
        if (typeof value === 'string') {
            return question.type === 'checkbox' ? [value] : value;
        }

        if (Array.isArray(value)) {
            return value.filter((selected): selected is string => typeof selected === 'string');
        }
    }

    return question.type === 'checkbox' ? [] : '';
}

function mapValueToLabel(question: QuestionConfig, rawValue: string): string {
    const option = (question.options ?? []).find((item) => item.value === rawValue);
    return option?.label ?? rawValue;
}

function renderAnswer(question: QuestionConfig): string {
    const storedValue = getStoredValue(question);

    if (Array.isArray(storedValue)) {
        if (storedValue.length === 0) {
            return '-';
        }

        return storedValue.map((value) => mapValueToLabel(question, value)).join(', ');
    }

    const normalized = storedValue.trim();
    if (normalized === '') {
        return '-';
    }

    if (question.type === 'radio' || question.type === 'select') {
        return mapValueToLabel(question, normalized);
    }

    return normalized;
}

function submitAssessorScore(): void {
    const payloadScores: Record<string, number> = {};
    const payloadNotes: Record<string, string> = {};

    for (const question of props.formConfig.questions ?? []) {
        if (!question.is_scoring || (question.scoringOptions ?? []).length === 0) {
            continue;
        }

        const selectedScore = assessorScores[question.name];
        if (selectedScore === null || Number.isNaN(selectedScore)) {
            continue;
        }

        payloadScores[question.name] = Number(selectedScore);
        payloadNotes[question.name] = (assessorNotes[question.name] ?? '').trim();
    }

    const payload = {
        scores: payloadScores,
        notes: payloadNotes,
    };

    console.log('Simpan Penilaian payload JSON:', JSON.stringify(payload));

    router.post(
        `/admin/penilaian/${props.mitra.kode_akses}`,
        payload,
        {
            preserveScroll: true,
            onStart: () => {
                isSubmittingScore.value = true;
            },
            onFinish: () => {
                isSubmittingScore.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Penilaian Wawancara" />

    <div class="mx-auto max-w-5xl space-y-4">
        <header class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-lg font-bold text-cyan-800 sm:text-xl">Penilaian Wawancara Mitra</h1>
                <Link href="/admin/seleksi_mitra" class="text-sm font-medium text-cyan-700 hover:underline">Kembali</Link>
            </div>
        </header>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-4 py-3">
                <h2 class="text-base font-bold text-white sm:text-lg">{{ props.formConfig.title }}</h2>
            </header>

            <div class="space-y-6 p-4 sm:p-6">
                <div class="space-y-2">
                    <p class="text-lg text-slate-800"><span class="font-semibold">Nama</span>: {{ props.mitra.nama_lengkap }}</p>
                    <p class="text-lg text-slate-800"><span class="font-semibold">NIK</span>: {{ props.mitra.nik }}</p>
                </div>

                <div v-if="props.formConfig.questions.length" class="space-y-6">
                    <div v-for="(question, index) in props.formConfig.questions" :key="question.name" class="space-y-3">
                        <div
                            v-if="question.type === 'label'"
                            class="rounded-md bg-slate-50 px-3 py-2 text-slate-800 [&_a]:text-cyan-700 [&_a]:underline [&_h1]:mb-2 [&_h1]:!text-3xl [&_h1]:!font-bold [&_h1]:!leading-tight [&_h2]:mb-2 [&_h2]:!text-2xl [&_h2]:!font-semibold [&_h2]:!leading-tight [&_h3]:mb-2 [&_h3]:!text-xl [&_h3]:!font-semibold [&_h3]:!leading-tight [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:!text-sm [&_p]:!leading-6 [&_ul]:list-disc [&_ul]:pl-5"
                            v-html="getLabelHtml(question)"
                        />

                        <template v-else>
                            <p class="text-base font-semibold text-slate-900">
                                {{ questionNumbers[question.name] }}. {{ question.label }}
                            </p>

                            <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                {{ renderAnswer(question) }}
                            </div>

                        </template>

                        <div v-if="question.is_scoring && (question.scoringOptions ?? []).length" class="space-y-2">
                            <p class="text-sm font-semibold text-slate-700">Pilih penilaian:</p>
                            <label
                                v-for="option in question.scoringOptions ?? []"
                                :key="`${question.name}-${option.label}-${option.score}`"
                                class="flex items-center gap-2 text-sm text-slate-700"
                            >
                                <input
                                    v-model.number="assessorScores[question.name]"
                                    type="radio"
                                    :name="`score-${question.name}`"
                                    :value="option.score"
                                    class="h-4 w-4 accent-red-600"
                                />
                                <span>{{ option.label }} ({{ option.score }})</span>
                            </label>
                            <div class="space-y-1 pt-1">
                                <label :for="`note-${question.name}`" class="text-sm font-semibold text-slate-700">Note (opsional)</label>
                                <input
                                    :id="`note-${question.name}`"
                                    v-model="assessorNotes[question.name]"
                                    type="text"
                                    class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-cyan-500"
                                    placeholder="Tambahkan catatan untuk pertanyaan ini"
                                />
                            </div>
                        </div>

                        <div v-else-if="question.is_scoring" class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            Pertanyaan ini ditandai scoring, tetapi <code>scoringOptions</code> belum diisi.
                        </div>
                    </div>
                </div>

                <p v-else class="text-sm text-slate-500">Belum ada pertanyaan aktif saat ini.</p>

                <div class="flex justify-end">
                    <button
                        type="button"
                        :disabled="isSubmittingScore"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-cyan-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700"
                        @click="submitAssessorScore"
                    >
                        {{ isSubmittingScore ? 'Menyimpan...' : 'Simpan Penilaian' }}
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
