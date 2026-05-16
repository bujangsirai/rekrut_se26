<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

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
    type: 'radio' | 'select' | 'checkbox' | 'textarea' | 'text' | 'label';
    is_showing_respondent?: boolean;
    is_showing_assessor?: boolean;
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

type AnswerValue = string | string[];

function getLabelHtml(question: QuestionConfig): string {
    return question.label || question.value || '';
}

interface SelectionFormConfig {
    title: string;
    description?: string;
    questions: QuestionConfig[];
}
const answers = reactive<Record<string, AnswerValue>>({});
const answerErrors = reactive<Record<string, string>>({});
const isSubmitting = ref(false);
const isSavingDraft = ref(false);
const isSubmitConfirmDialogOpen = ref(false);

const props = defineProps<{
    mitra: {
        nik: string;
        nama_lengkap: string;
        kode_akses: string;
        posisi_dilamar: string;
        status_kelulusan: string;
        jawaban_kuesioner?: Record<string, unknown> | null;
        [key: string]: any;
    };
    formConfig: SelectionFormConfig;
}>();

const page = usePage();
const serverErrors = computed<Record<string, string>>(() => (page.props.errors ?? {}) as Record<string, string>);

function getPrefilledAnswer(question: QuestionConfig): AnswerValue {
    const questionName = question.name;
    const finalAnswers = props.mitra.jawaban_kuesioner;
    if (!finalAnswers || typeof finalAnswers !== 'object') {
        return question.type === 'checkbox' ? [] : '';
    }

    const item = (finalAnswers as Record<string, unknown>)[questionName];
    if (typeof item === 'string') {
        return question.type === 'checkbox' ? [item] : item;
    }

    if (Array.isArray(item)) {
        return item.filter((value): value is string => typeof value === 'string');
    }

    if (item && typeof item === 'object' && 'value' in item) {
        const answerValue = (item as { value?: unknown }).value;
        if (typeof answerValue === 'string') {
            return question.type === 'checkbox' ? [answerValue] : answerValue;
        }

        if (Array.isArray(answerValue)) {
            return answerValue.filter((value): value is string => typeof value === 'string');
        }
    }

    return question.type === 'checkbox' ? [] : '';
}

for (const question of props.formConfig.questions ?? []) {
    answers[question.name] = getPrefilledAnswer(question);
    answerErrors[question.name] = '';
}

const respondentQuestions = computed(() => {
    return (props.formConfig.questions ?? []).filter((question) => {
        if (typeof question.is_showing_respondent === 'boolean') {
            return question.is_showing_respondent;
        }

        return question.is_showing !== false;
    });
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
    if (question.type === 'label' || question.type === 'checkbox') {
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

    const currentAnswer = answers[question.name];
    const currentValue = typeof currentAnswer === 'string' ? currentAnswer : '';
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

function getCheckboxAnswers(questionName: string): string[] {
    const currentAnswer = answers[questionName];
    if (!Array.isArray(currentAnswer)) {
        return [];
    }

    return currentAnswer
        .filter((value): value is string => typeof value === 'string')
        .map((value) => value.trim())
        .filter((value) => value !== '');
}

function handleCheckboxChange(questionName: string, optionValue: string, event: Event): void {
    const target = event.target as HTMLInputElement | null;
    const checked = target?.checked ?? false;
    const currentValues = getCheckboxAnswers(questionName);
    const nextValues = checked
        ? Array.from(new Set([...currentValues, optionValue]))
        : currentValues.filter((value) => value !== optionValue);

    answers[questionName] = nextValues;
}

function isRadioSelected(questionName: string, optionValue: string): boolean {
    const currentAnswer = answers[questionName];
    return typeof currentAnswer === 'string' && currentAnswer === optionValue;
}

function isCheckboxSelected(questionName: string, optionValue: string): boolean {
    return getCheckboxAnswers(questionName).includes(optionValue);
}

function submitAnswers(): void {
    let hasClientError = false;

    for (const question of respondentQuestions.value) {
        if (question.type === 'label') {
            continue;
        }

        if (question.type === 'checkbox') {
            const selectedValues = getCheckboxAnswers(question.name);

            if (question.required && selectedValues.length === 0) {
                answerErrors[question.name] = 'Jawaban wajib diisi.';
                hasClientError = true;
                continue;
            }

            answerErrors[question.name] = '';
            continue;
        }

        const currentAnswer = answers[question.name];
        const currentValue = typeof currentAnswer === 'string' ? currentAnswer.trim() : '';

        if (question.required && currentValue === '') {
            answerErrors[question.name] = 'Jawaban wajib diisi.';
            hasClientError = true;
            continue;
        }

        if (currentValue === '') {
            answerErrors[question.name] = '';
            continue;
        }

        if (question.is_validation) {
            validateAnswerOnKeyup(question);
            if (answerErrors[question.name]) {
                hasClientError = true;
                continue;
            }
        } else {
            answerErrors[question.name] = '';
        }
    }

    const payloadAnswers: Record<string, AnswerValue> = {};
    for (const question of respondentQuestions.value) {
        if (question.type === 'label') {
            continue;
        }

        if (question.type === 'checkbox') {
            payloadAnswers[question.name] = getCheckboxAnswers(question.name);
            continue;
        }

        const currentAnswer = answers[question.name];
        payloadAnswers[question.name] = typeof currentAnswer === 'string' ? currentAnswer.trim() : '';
    }

    if (hasClientError) {
        console.log('Kirim Jawaban payload (client has validation errors):', payloadAnswers);
    }

    router.post(
        `/seleksi/${props.mitra.kode_akses}`,
        {
            answers: payloadAnswers,
        },
        {
            preserveScroll: true,
            onStart: () => {
                isSubmitting.value = true;
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
}

function openSubmitConfirmation(): void {
    isSubmitConfirmDialogOpen.value = true;
}

function confirmSubmitAnswers(): void {
    isSubmitConfirmDialogOpen.value = false;
    submitAnswers();
}

function saveDraftAnswers(): void {
    const payloadAnswers: Record<string, AnswerValue> = {};

    for (const question of respondentQuestions.value) {
        if (question.type === 'label') {
            continue;
        }

        if (question.type === 'checkbox') {
            payloadAnswers[question.name] = getCheckboxAnswers(question.name);
            continue;
        }

        const currentAnswer = answers[question.name];
        payloadAnswers[question.name] = typeof currentAnswer === 'string' ? currentAnswer.trim() : '';
    }

    console.log('Simpan Sementara payload:', payloadAnswers);

    router.post(
        `/seleksi/${props.mitra.kode_akses}/sementara`,
        {
            answers: payloadAnswers,
        },
        {
            preserveScroll: true,
            onStart: () => {
                isSavingDraft.value = true;
            },
            onFinish: () => {
                isSavingDraft.value = false;
            },
        },
    );
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
                                class="rounded-md bg-slate-50 px-3 py-2 text-slate-800 [&_a]:text-cyan-700 [&_a]:underline [&_h1]:mb-2 [&_h1]:!text-3xl [&_h1]:!font-bold [&_h1]:!leading-tight [&_h2]:mb-2 [&_h2]:!text-2xl [&_h2]:!font-semibold [&_h2]:!leading-tight [&_h3]:mb-2 [&_h3]:!text-xl [&_h3]:!font-semibold [&_h3]:!leading-tight [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:!text-sm [&_p]:!leading-6 [&_ul]:list-disc [&_ul]:pl-5"
                                v-html="getLabelHtml(question)"
                            />
                            <label v-if="question.type !== 'label'" :for="question.name" class="text-sm font-semibold text-slate-800">
                                {{ respondentQuestionNumbers[question.name] }}. {{ question.label }}<span v-if="question.required">*</span>
                            </label>

                            <div v-if="question.type === 'radio'" class="flex flex-wrap gap-3">
                                <label
                                    v-for="option in question.options ?? []"
                                    :key="option.value"
                                    :class="[
                                        'inline-flex items-center gap-2 rounded-md px-2 py-1 text-sm transition',
                                        isRadioSelected(question.name, option.value) ? 'bg-cyan-50 font-medium text-cyan-700' : 'text-slate-700',
                                    ]"
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
                                v-model="answers[question.name] as string"
                                class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-cyan-500"
                            >
                                <option value="">{{ question.placeholder ?? 'Pilih jawaban' }}</option>
                                <option v-for="option in question.options ?? []" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>

                            <div v-else-if="question.type === 'checkbox'" class="flex flex-wrap gap-3">
                                <label
                                    v-for="option in question.options ?? []"
                                    :key="option.value"
                                    :class="[
                                        'inline-flex items-center gap-2 rounded-md px-2 py-1 text-sm transition',
                                        isCheckboxSelected(question.name, option.value) ? 'bg-cyan-50 font-medium text-cyan-700' : 'text-slate-700',
                                    ]"
                                >
                                    <input
                                        type="checkbox"
                                        :value="option.value"
                                        :checked="isCheckboxSelected(question.name, option.value)"
                                        class="h-4 w-4 accent-cyan-600"
                                        @change="handleCheckboxChange(question.name, option.value, $event)"
                                    />
                                    {{ option.label }}
                                </label>
                            </div>

                            <textarea
                                v-else-if="question.type === 'textarea'"
                                :id="question.name"
                                v-model="answers[question.name] as string"
                                rows="3"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-cyan-500"
                                :placeholder="question.placeholder ?? 'Tulis jawaban Anda di sini...'"
                                @keyup="validateAnswerOnKeyup(question)"
                            />

                            <input
                                v-else-if="question.type !== 'label'"
                                :id="question.name"
                                v-model="answers[question.name] as string"
                                type="text"
                                class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-800 outline-none focus:border-cyan-500"
                                :placeholder="question.placeholder ?? 'Tulis jawaban...'"
                                @keyup="validateAnswerOnKeyup(question)"
                            />

                            <p v-if="answerErrors[question.name]" class="text-xs text-red-600">
                                {{ answerErrors[question.name] }}
                            </p>
                            <p v-else-if="serverErrors[`answers.${question.name}`]" class="text-xs text-red-600">
                                {{ serverErrors[`answers.${question.name}`] }}
                            </p>
                            <p v-if="question.helpText && question.type !== 'label'" class="text-xs text-slate-500">
                                {{ question.helpText }}
                            </p>
                        </div>
                        <p v-if="!respondentQuestions.length" class="text-sm text-slate-500">Belum ada pertanyaan aktif saat ini.</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            :disabled="isSavingDraft || isSubmitting"
                            class="inline-flex h-10 items-center justify-center rounded-lg border border-cyan-600 bg-white px-5 text-sm font-semibold text-cyan-700 shadow-sm transition hover:bg-cyan-50"
                            @click="saveDraftAnswers"
                        >
                            {{ isSavingDraft ? 'Menyimpan...' : 'Simpan Sementara' }}
                        </button>
                        <button
                            type="button"
                            :disabled="isSubmitting || isSavingDraft"
                            class="inline-flex h-10 items-center justify-center rounded-lg bg-cyan-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700"
                            @click="openSubmitConfirmation"
                        >
                            {{ isSubmitting ? 'Mengirim...' : 'Kirim Jawaban' }}
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <Dialog :open="isSubmitConfirmDialogOpen" @update:open="isSubmitConfirmDialogOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Konfirmasi Submit</DialogTitle>
                <DialogDescription>Apakah kamu yakin ingin men submit? pastikan jawaban benar.</DialogDescription>
            </DialogHeader>
            <DialogFooter class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                <Button variant="outline" @click="isSubmitConfirmDialogOpen = false">Batal</Button>
                <Button class="bg-cyan-600 text-white hover:bg-cyan-700" @click="confirmSubmitAnswers">Ya, Submit</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
