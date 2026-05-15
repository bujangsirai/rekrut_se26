<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

defineOptions({
    // @ts-ignore
    layout: null,
});

interface QuestionOption {
    label: string;
    value: string;
}

interface QuestionConfig {
    name: string;
    label: string;
    value?: string;
    type: 'radio' | 'select' | 'textarea' | 'text' | 'label';
    is_showing?: boolean;
    is_scoring?: boolean;
    is_validation?: boolean;
    required?: boolean;
    placeholder?: string;
    helpText?: string;
    validationRegex?: string;
    validationMessage?: string;
    options?: QuestionOption[];
}

function getLabelHtml(question: QuestionConfig): string {
    return question.label || question.value || '';
}

interface SelectionFormConfig {
    title: string;
    description?: string;
    questions: QuestionConfig[];
}
const answers = reactive<Record<string, string>>({});
const answerErrors = reactive<Record<string, string>>({});

const props = defineProps<{
    mitra: {
        nik: string;
        nama_lengkap: string;
        posisi_dilamar: string;
        status_kelulusan: string;
        [key: string]: any;
    };
    formConfig: SelectionFormConfig;
}>();

for (const question of props.formConfig.questions ?? []) {
    answers[question.name] = '';
    answerErrors[question.name] = '';
}

const respondentQuestions = computed(() => {
    return (props.formConfig.questions ?? []).filter((question) => question.is_showing !== false);
});

const respondentQuestionNumbers = computed(() => {
    const numbers: Record<string, number> = {};
    let current = 0;

    for (const question of respondentQuestions.value) {
        if (question.type !== 'label') {
            current += 1;
            numbers[question.name] = current;
        }
    }

    return numbers;
});

function validateAnswerOnKeyup(question: QuestionConfig): void {
    if (question.type === 'label') {
        return;
    }

    if (!question.is_validation) {
        answerErrors[question.name] = '';
        return;
    }

    const pattern = question.validationRegex?.trim();
    if (!pattern) {
        answerErrors[question.name] = '';
        return;
    }

    const currentValue = answers[question.name] ?? '';
    if (currentValue === '') {
        answerErrors[question.name] = '';
        return;
    }

    try {
        const regex = new RegExp(pattern);
        answerErrors[question.name] = regex.test(currentValue)
            ? ''
            : (question.validationMessage?.trim() || 'Format jawaban tidak sesuai.');
    } catch {
        answerErrors[question.name] = 'Konfigurasi RegExp tidak valid.';
    }
}
</script>

<template>
    <Head title="Form Wawancara SE2026 KSB" />

    <div class="min-h-screen bg-linear-to-b from-cyan-50 via-slate-100 to-slate-200 px-2 py-3 sm:px-6 sm:py-6">
        <div class="mx-auto max-w-5xl space-y-4">
            <header class="rounded-xl border border-cyan-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h1 class="text-lg font-bold text-cyan-800 sm:text-xl">Form Wawancara SE2026 KSB</h1>
                    <Link href="/" class="text-sm font-medium text-cyan-700 hover:underline">Kembali</Link>
                </div>
            </header>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <header class="bg-linear-to-r from-cyan-700 to-cyan-600 px-4 py-3">
                    <h2 class="text-base font-bold text-white sm:text-lg">{{ props.formConfig.title }}</h2>
                </header>

                <div class="space-y-5 p-4 sm:p-6">
                    <div class="space-y-2">
                        <p class="text-md text-slate-800"><span class="font-semibold text-slate-800">Nama</span>: {{ props.mitra.nama_lengkap }}</p>
                        <p class="text-md text-slate-800"><span class="font-semibold text-slate-800">NIK</span>: {{ props.mitra.nik }}</p>
                    </div>

                    <div class="grid gap-5">
                        <div v-for="question in respondentQuestions" :key="question.name" class="space-y-2">
                            <div
                                v-if="question.type === 'label'"
                                class="rounded-md bg-slate-50 px-3 py-2 text-slate-800 [&_a]:text-cyan-700 [&_a]:underline [&_h1]:mb-2 [&_h1]:text-3xl [&_h1]:font-bold [&_h1]:leading-tight [&_h2]:mb-2 [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:leading-tight [&_h3]:mb-2 [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:leading-tight [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:text-sm [&_p]:leading-6 [&_ul]:list-disc [&_ul]:pl-5"
                                v-html="getLabelHtml(question)"
                            />
                            <label v-if="question.type !== 'label'" :for="question.name" class="text-sm font-semibold text-slate-800">
                                {{ respondentQuestionNumbers[question.name] }}. {{ question.label }}<span v-if="question.required">*</span>
                            </label>

                            <div v-if="question.type === 'radio'" class="flex flex-wrap gap-3">
                                <label
                                    v-for="option in question.options ?? []"
                                    :key="option.value"
                                    class="inline-flex items-center gap-2 text-sm text-slate-700"
                                >
                                    <input
                                        v-model="answers[question.name]"
                                        type="radio"
                                        :name="question.name"
                                        :value="option.value"
                                        class="h-4 w-4 accent-cyan-600"
                                    />
                                    {{ option.label }}
                                </label>
                            </div>

                            <select
                                v-else-if="question.type === 'select'"
                                :id="question.name"
                                v-model="answers[question.name]"
                                class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-cyan-500"
                            >
                                <option value="">{{ question.placeholder ?? 'Pilih jawaban' }}</option>
                                <option v-for="option in question.options ?? []" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>

                            <textarea
                                v-else-if="question.type === 'textarea'"
                                :id="question.name"
                                v-model="answers[question.name]"
                                rows="4"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-cyan-500"
                                :placeholder="question.placeholder ?? 'Tulis jawaban Anda di sini...'"
                                @keyup="validateAnswerOnKeyup(question)"
                            />

                            <input
                                v-else-if="question.type !== 'label'"
                                :id="question.name"
                                v-model="answers[question.name]"
                                type="text"
                                class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-cyan-500"
                                :placeholder="question.placeholder ?? 'Tulis jawaban...'"
                                @keyup="validateAnswerOnKeyup(question)"
                            />

                            <p v-if="answerErrors[question.name]" class="text-xs text-red-600">
                                {{ answerErrors[question.name] }}
                            </p>
                            <p v-if="question.helpText && question.type !== 'label'" class="text-xs text-slate-500">
                                {{ question.helpText }}
                            </p>
                        </div>
                        <p v-if="!respondentQuestions.length" class="text-sm text-slate-500">Belum ada pertanyaan aktif saat ini.</p>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="button"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-cyan-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700"
                        >
                            Kirim Jawaban (Dummy)
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
