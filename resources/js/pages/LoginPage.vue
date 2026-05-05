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

        <div class="relative w-full max-w-md rounded-[2rem] border border-white/60 bg-white/80 p-6 shadow-xl backdrop-blur-sm sm:p-8">
            <div class="mx-auto mb-7 flex h-20 w-20 items-center justify-center rounded-3xl bg-white shadow-md">
                <ArrowRightToLine class="h-9 w-9 text-slate-700" />
            </div>

            <div class="mb-7 text-center">
                <h1 class="text-3xl font-bold text-slate-900">Sign in</h1>
                <p class="mt-2 text-base leading-relaxed text-slate-600">Login pengguna organik BPS untuk melanjutkan ke sistem.</p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <div class="flex h-14 items-center gap-3 rounded-2xl border border-slate-200 bg-slate-100/90 px-4">
                        <Mail class="h-5 w-5 text-slate-500" />
                        <input
                            v-model="form.username"
                            type="text"
                            autocomplete="username"
                            placeholder="Username"
                            class="w-full bg-transparent text-base text-slate-800 outline-none placeholder:text-slate-500"
                        />
                    </div>
                    <p v-if="form.errors.username" class="mt-1.5 text-sm text-rose-600">{{ form.errors.username }}</p>
                </div>

                <div>
                    <div class="flex h-14 items-center gap-3 rounded-2xl border border-slate-200 bg-slate-100/90 px-4">
                        <Lock class="h-5 w-5 text-slate-500" />
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            placeholder="Password"
                            class="w-full bg-transparent text-base text-slate-800 outline-none placeholder:text-slate-500"
                        />
                        <button type="button" class="text-slate-500 transition hover:text-slate-700" @click="showPassword = !showPassword">
                            <EyeOff v-if="showPassword" class="h-5 w-5" />
                            <Eye v-else class="h-5 w-5" />
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="mt-1.5 text-sm text-rose-600">{{ form.errors.password }}</p>
                </div>

                <div class="flex justify-end">
                    <a href="#" class="text-sm font-medium text-slate-700 transition hover:text-slate-900 hover:underline">Forgot password?</a>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="h-14 w-full rounded-2xl bg-linear-to-b from-slate-800 to-slate-950 text-xl font-semibold text-white shadow-lg transition hover:from-slate-700 hover:to-slate-900 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ form.processing ? 'Processing...' : 'Get Started' }}
                </button>
            </form>

            <div class="my-6 flex items-center gap-4 text-sm text-slate-500">
                <span class="h-px flex-1 bg-slate-200" />
                <span>Or sign in with</span>
                <span class="h-px flex-1 bg-slate-200" />
            </div>

            <div class="grid grid-cols-3 gap-3">
                <button type="button" class="h-12 rounded-xl border border-slate-200 bg-white text-lg font-semibold text-slate-700 shadow-xs">
                    G
                </button>
                <button type="button" class="h-12 rounded-xl border border-slate-200 bg-white text-lg font-semibold text-slate-700 shadow-xs">
                    f
                </button>
                <button type="button" class="h-12 rounded-xl border border-slate-200 bg-white text-lg font-semibold text-slate-700 shadow-xs">
                    
                </button>
            </div>

            <p class="mt-6 text-center text-sm text-slate-600">
                Kembali ke halaman publik?
                <Link href="/" class="font-semibold text-cyan-700 hover:underline">Buka Rekrutmen Mitra</Link>
            </p>
        </div>
    </div>
</template>
