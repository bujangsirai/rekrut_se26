<script setup lang="ts">
import LayoutAdmin from '@/components/layouts/LayoutAdmin.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

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
    type: 'radio' | 'select' | 'textarea' | 'text' | 'label';
    scoring?: number;
    required?: boolean;
    placeholder?: string;
    helpText?: string;
    rows?: number;
    maxLength?: number;
    options?: QuestionOption[];
    scoringOptions?: ScoringOption[];
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
        [key: string]: unknown;
    };
    formConfig: SelectionFormConfig;
}>();

defineOptions({
    layout: LayoutAdmin,
});

const assessorScores = reactive<Record<string, number | null>>({});

for (const question of props.formConfig.questions ?? []) {
    assessorScores[question.name] = null;
}

const answerableQuestions = computed(() => {
    return props.formConfig.questions.filter((question) => question.type !== 'label');
});

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

                <div v-if="answerableQuestions.length" class="space-y-6">
                    <div v-for="(question, index) in props.formConfig.questions" :key="question.name" class="space-y-3">
                        <div
                            v-if="question.type === 'label'"
                            class="rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-800"
                            v-html="question.label"
                        />

                        <template v-else>
                            <p class="text-base font-semibold text-slate-900">
                                {{ questionNumbers[question.name] }}. {{ question.label }}
                            </p>

                            <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                Jawaban responden ditampilkan di sini (menunggu integrasi penyimpanan jawaban).
                            </div>

                            <div v-if="(question.scoringOptions ?? []).length" class="space-y-2">
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
                            </div>

                            <div v-else class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                Pertanyaan ini tidak memiliki <code>scoringOptions</code>. Skor default:
                                <span class="font-semibold">{{ question.scoring ?? 0 }}</span>
                            </div>
                        </template>
                    </div>
                </div>

                <p v-else class="text-sm text-slate-500">Belum ada pertanyaan aktif saat ini.</p>

                <div class="flex justify-end">
                    <button
                        type="button"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-cyan-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700"
                    >
                        Simpan Penilaian (Dummy)
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
