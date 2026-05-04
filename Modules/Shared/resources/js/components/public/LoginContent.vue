<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { media } from '@/lib/media';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface PublicLoginContentPageProps {
    [key: string]: unknown;
    auth?: {
        user?: unknown | null;
    };
    flash?: {
        success?: string | null;
        error?: string | null;
        warning?: string | null;
        info?: string | null;
    } | null;
}

const emit = defineEmits<{
    (event: 'success'): void;
}>();

const page = usePage<PublicLoginContentPageProps>();
const isPasswordVisible = ref(false);

const form = useForm({
    username: '',
    password: '',
});

const flashMessage = computed(() => page.props.flash?.error ?? null);
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));

watch(flashMessage, (value) => {
    if (value) {
        form.clearErrors();
    }
});

function submit(): void {
    form.post('/login-password', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('password');
            emit('success');
        },
    });
}

function togglePasswordVisibility(): void {
    isPasswordVisible.value = !isPasswordVisible.value;
}
</script>

<template>
    <div>
        <div class="space-y-1 bg-muted/20 px-4 pt-4 pb-1 text-center">
            <img :src="media + 'img/logo/saku.png'" alt="Logo SAKU" class="mx-auto h-20 w-20 object-contain" />
            <div class="space-y-0.5 text-center">
                <h2 class="text-lg font-semibold tracking-tight text-foreground">Selamat datang di SAKU</h2>
                <p class="text-[11px] text-muted-foreground">Silahkan login untuk masuk ke aplikasi internal.</p>
            </div>
        </div>

        <div v-if="isAuthenticated" class="px-4 pt-2 pb-4">
            <Button as-child class="h-8 w-full rounded-lg text-[13px]" variant="default">
                <Link href="/app">Kembali ke Aplikasi SAKU</Link>
            </Button>
        </div>

        <form v-else class="space-y-2.5 px-4 pt-2 pb-4" @submit.prevent="submit">
            <div v-if="flashMessage" class="rounded-xl border border-destructive/20 bg-destructive/10 px-3 py-2 text-[11px] text-destructive">
                {{ flashMessage }}
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-foreground" for="username">Username</label>
                <Input
                    id="username"
                    v-model="form.username"
                    type="text"
                    autocomplete="username"
                    class="h-8 rounded-lg border-border/70 bg-muted/70 px-2.5 text-xs shadow-none placeholder:text-xs"
                    placeholder="Masukkan username"
                />
                <p v-if="form.errors.username" class="text-[11px] text-destructive">
                    {{ form.errors.username }}
                </p>
            </div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-foreground" for="password">Password</label>
                <div class="relative">
                    <Input
                        id="password"
                        v-model="form.password"
                        :type="isPasswordVisible ? 'text' : 'password'"
                        autocomplete="current-password"
                        class="h-8 rounded-lg border-border/70 bg-muted/70 px-2.5 pr-8 text-xs shadow-none placeholder:text-xs"
                        placeholder="Masukkan password"
                    />
                    <button
                        class="absolute inset-y-0 right-0 flex w-8 items-center justify-center text-muted-foreground transition hover:text-foreground"
                        type="button"
                        @click="togglePasswordVisibility"
                    >
                        <EyeOff v-if="isPasswordVisible" class="h-3 w-3" />
                        <Eye v-else class="h-3 w-3" />
                    </button>
                </div>
                <p v-if="form.errors.password" class="text-[11px] text-destructive">
                    {{ form.errors.password }}
                </p>
            </div>

            <div class="flex flex-col gap-2 pt-0.5">
                <Button
                    :disabled="form.processing"
                    class="h-8 w-full cursor-pointer rounded-lg text-[13px] shadow-sm"
                    type="submit"
                    variant="default"
                >
                    {{ form.processing ? 'Memproses...' : 'Login' }}
                </Button>

                <div class="flex items-center gap-2 text-[9px] font-medium tracking-[0.22em] text-muted-foreground/80 uppercase">
                    <span class="h-px flex-1 bg-border" />
                    <span>or</span>
                    <span class="h-px flex-1 bg-border" />
                </div>

                <Button as-child class="h-8 w-full rounded-lg text-[13px]" type="button" variant="secondary">
                    <a href="/login_sso">Login with SSO BPS</a>
                </Button>
            </div>
        </form>
    </div>
</template>
