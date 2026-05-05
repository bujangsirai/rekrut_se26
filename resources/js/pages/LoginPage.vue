<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRightToLine, Eye, EyeOff, Lock, Mail } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({
    // @ts-ignore
    layout: null,
});

const showPassword = ref(false);

const form = useForm({
    username: '',
    password: '',
});

function submit(): void {
    form.post('/login-password', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('password');
        },
    });
}
</script>

<template>
    <Head title="Login Organik" />

    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-linear-to-br from-sky-100 via-cyan-50 to-indigo-100 px-4 py-10"
    >
        <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-cyan-300/30 blur-3xl" />
        <div class="pointer-events-none absolute -right-20 -bottom-24 h-80 w-80 rounded-full bg-indigo-300/30 blur-3xl" />

        <div class="relative w-full max-w-sm rounded-[1.5rem] border border-white/60 bg-white/80 p-5 shadow-xl backdrop-blur-sm sm:p-6">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-md">
                <ArrowRightToLine class="h-7 w-7 text-slate-700" />
            </div>

            <div class="mb-5 text-center">
                <h1 class="text-2xl font-bold text-slate-900">Sign in</h1>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-600">Login pengguna organik BPS untuk melanjutkan ke sistem.</p>
            </div>

            <form class="space-y-3" @submit.prevent="submit">
                <div>
                    <div class="flex h-12 items-center gap-3 rounded-xl border border-slate-200 bg-slate-100/90 px-3.5">
                        <Mail class="h-5 w-5 text-slate-500" />
                        <input
                            v-model="form.username"
                            type="text"
                            autocomplete="username"
                            placeholder="Username"
                            class="w-full bg-transparent text-sm text-slate-800 outline-none placeholder:text-slate-500"
                        />
                    </div>
                    <p v-if="form.errors.username" class="mt-1 text-xs text-rose-600">{{ form.errors.username }}</p>
                </div>

                <div>
                    <div class="flex h-12 items-center gap-3 rounded-xl border border-slate-200 bg-slate-100/90 px-3.5">
                        <Lock class="h-5 w-5 text-slate-500" />
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            placeholder="Password"
                            class="w-full bg-transparent text-sm text-slate-800 outline-none placeholder:text-slate-500"
                        />
                        <button type="button" class="text-slate-500 transition hover:text-slate-700" @click="showPassword = !showPassword">
                            <EyeOff v-if="showPassword" class="h-5 w-5" />
                            <Eye v-else class="h-5 w-5" />
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="mt-2 h-12 w-full rounded-xl bg-linear-to-b from-slate-800 to-slate-950 text-base font-semibold text-white shadow-lg transition hover:from-slate-700 hover:to-slate-900 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ form.processing ? 'Processing...' : 'Login' }}
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-600">
                Kembali ke halaman publik?
                <Link href="/" class="font-semibold text-cyan-700 hover:underline">Buka Rekrutmen Mitra</Link>
            </p>
        </div>
    </div>
</template>
