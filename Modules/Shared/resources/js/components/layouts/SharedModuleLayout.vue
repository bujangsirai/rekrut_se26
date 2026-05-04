<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { media } from '@/lib/media';
import { Link, usePage } from '@inertiajs/vue3';
import { Bell, ChevronDown, CircleUserRound, Eye, House, KeyRound, LayoutGrid, LogOut, Pencil } from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed, ref } from 'vue';
import UpdateMyEmailDialog from './UpdateMyEmailDialog.vue';
import UpdateMyPasswordDialog from './UpdateMyPasswordDialog.vue';

interface SharedNavItem {
    key: string;
    label: string;
    href?: string;
    icon?: Component;
    match?: string[];
    exact?: boolean;
}

interface SharedReminderItem {
    key: string;
    title: string;
    description: string;
}

const fixedHeader = {
    title: 'SAKU BPS KSB',
    subtitle: 'Satu Aplikasi untuk Kinerja Unggul',
};

const authUser = computed(() => (page.props.auth as any)?.user ?? null);

const userFotoUrl = computed(() => {
    const foto = authUser.value?.url_foto;
    return foto ? (foto.startsWith('http') ? foto : media + foto) : media + 'img/avatar/default.png';
});

const authRoles = computed(() => (page.props.auth as any)?.roles ?? []);

const rolesOpen = ref(false);
const passwordModalOpen = ref(false);
const emailModalOpen = ref(false);
const profileModalOpen = ref(false);

const fixedNavItems: SharedNavItem[] = [
    { key: 'beranda', label: 'Beranda', icon: House, href: '/app', match: ['/app'] },
    { key: 'menu-cepat', label: 'Menu Cepat', icon: LayoutGrid, href: '/menu-cepat', match: ['/menu-cepat'], exact: true },
    { key: 'notifikasi', label: 'Notifkasi', icon: Bell, href: '/notification', match: ['/notification'], exact: true },
    { key: 'landing-page', label: 'Saku Eksternal', icon: CircleUserRound, href: '/welcome', match: ['/'] },
];

const fixedReminderItems: SharedReminderItem[] = [
    {
        key: 'review',
        title: 'Tes',
        description: 'SATU',
    },
    {
        key: 'sync',
        title: 'Tes dua',
        description: 'DUA',
    },
];

const page = usePage();

const currentPath = computed(() => {
    const [path] = page.url.split(/[?#]/);
    return path || '/';
});

const navItemsWithActive = computed(() =>
    fixedNavItems.map((item) => ({
        ...item,
        active: isNavItemActive(currentPath.value, item),
    })),
);

const desktopNavItems = computed(() => navItemsWithActive.value);
const mobileNavItems = computed(() => navItemsWithActive.value.slice(0, 4));

function isNavItemActive(path: string, item: SharedNavItem): boolean {
    const patterns = item.match ?? (item.href ? [item.href] : []);
    const normalizedPath = path.replace(/\/+$/, '') || '/';

    if (item.exact) {
        return patterns.some((pattern) => {
            const normalizedPattern = pattern.replace(/\/+$/, '') || '/';
            return normalizedPath === normalizedPattern;
        });
    }

    return patterns.some((pattern) => {
        const normalizedPattern = pattern.replace(/\/+$/, '') || '/';
        if (normalizedPattern === '/') {
            return normalizedPath === '/';
        }
        return normalizedPath === normalizedPattern || normalizedPath.startsWith(`${normalizedPattern}/`);
    });
}
</script>

<template>
    <div class="min-h-screen">
        <div class="sticky top-0 z-30">
            <div class="h-2 bg-linear-to-r from-primary via-primary/80 to-accent" />

            <header class="border-b border-border bg-background/90 backdrop-blur">
                <div class="mx-auto flex h-13 max-w-400 items-center justify-between px-4 sm:h-14 sm:px-6">
                    <!-- Kiri: Mobile → Avatar + Nama -->
                    <div @click="profileModalOpen = true" class="flex cursor-pointer items-center gap-2.5 lg:hidden">
                        <Avatar class="h-8 w-8 ring-2 ring-primary/80 ring-offset-1 ring-offset-background">
                            <AvatarImage
                                v-if="userFotoUrl"
                                :src="userFotoUrl"
                                :alt="authUser?.nama ?? 'Foto'"
                                class="h-full w-full object-cover object-top"
                            />
                            <AvatarFallback class="bg-muted text-[10px] font-bold text-primary">
                                <img :src="media + 'img/avatar/default.png'" alt="Default Foto" class="h-full w-full object-cover object-top" />
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <p class="text-sm leading-tight font-semibold text-foreground">{{ authUser?.nama ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Kiri: Desktop → Logo + Judul -->
                    <div class="hidden items-center gap-3 lg:flex">
                        <img :src="media + 'img/logo/saku.png'" alt="SAKU" class="h-12 w-auto sm:h-14" />
                        <div>
                            <p class="text-sm font-semibold text-foreground">{{ fixedHeader.title }}</p>
                            <p class="text-xs text-muted-foreground">{{ fixedHeader.subtitle }}</p>
                        </div>
                    </div>

                    <!-- Kanan: Logout (sama di semua ukuran) -->
                    <Button as-child variant="default" class="cursor-pointer rounded-xl border-border px-3">
                        <Link href="/logout" class="inline-flex items-center gap-2">
                            <LogOut class="h-4 w-4" />
                            <span class="text-sm">Logout</span>
                        </Link>
                    </Button>
                </div>
            </header>
        </div>

        <main class="mx-auto grid max-w-400 gap-4 px-3 py-4 sm:gap-5 sm:px-4 sm:py-5 lg:grid-cols-[176px_minmax(0,1fr)]">
            <aside class="hidden lg:flex lg:flex-col lg:gap-4">
                <Card class="gap-0 rounded-2xl border-border p-1.5">
                    <CardContent class="px-2 pt-0">
                        <div class="mt-1 flex flex-col items-center text-center">
                            <!-- Foto -->
                            <Avatar class="h-20 w-20 shadow-inner ring-3 ring-primary/90 ring-offset-2 ring-offset-card">
                                <AvatarImage
                                    v-if="userFotoUrl"
                                    :src="userFotoUrl"
                                    :alt="authUser?.nama ?? 'Foto'"
                                    class="h-full w-full object-cover object-top"
                                />
                                <AvatarFallback class="bg-muted text-muted-foreground">
                                    <img :src="media + 'img/avatar/default.png'" alt="Default Foto" class="h-full w-full object-cover object-top" />
                                </AvatarFallback>
                            </Avatar>

                            <!-- Nama + Lihat Profil -->
                            <Dialog v-model:open="profileModalOpen">
                                <div class="mt-3 flex items-center justify-center gap-1">
                                    <h2 class="text-sm leading-tight font-bold tracking-tight text-foreground">
                                        {{ authUser?.nama ?? '-' }}
                                    </h2>
                                    <DialogTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="group relative h-5 w-5 shrink-0 cursor-pointer overflow-visible rounded-full text-muted-foreground hover:bg-muted hover:text-foreground"
                                        >
                                            <Eye class="h-3 w-3" />
                                            <span
                                                class="pointer-events-none absolute bottom-full left-1/2 mb-1.5 hidden -translate-x-1/2 rounded-md bg-foreground px-2 py-1 text-[10px] font-medium whitespace-nowrap text-background group-hover:block"
                                            >
                                                Lihat Profil
                                            </span>
                                        </Button>
                                    </DialogTrigger>
                                </div>

                                <!-- Divider -->
                                <div class="my-3 w-full border-t border-border" />

                                <!-- Collapsible Role -->
                                <Collapsible v-if="authRoles.length" v-model:open="rolesOpen" class="w-full">
                                    <CollapsibleTrigger
                                        class="flex w-full cursor-pointer items-center justify-between rounded-lg px-2 py-1.5 text-[10px] font-semibold text-foreground transition-colors hover:bg-muted"
                                    >
                                        <span>Role</span>
                                        <ChevronDown class="h-3 w-3 transition-transform duration-200" :class="{ 'rotate-180': rolesOpen }" />
                                    </CollapsibleTrigger>
                                    <CollapsibleContent>
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            <span
                                                v-for="role in authRoles"
                                                :key="role"
                                                class="rounded-full bg-primary/10 px-2 py-0.5 text-[9px] font-semibold text-primary"
                                            >
                                                {{ role }}
                                            </span>
                                        </div>
                                    </CollapsibleContent>
                                </Collapsible>

                                <DialogContent class="max-w-sm max-h-[90vh] overflow-y-auto">
                                    <DialogHeader>
                                        <DialogTitle>Profil Saya</DialogTitle>
                                        <DialogDescription>Informasi akun yang sedang login.</DialogDescription>
                                    </DialogHeader>

                                    <div class="flex flex-col items-center gap-5 pt-1">
                                        <!-- Foto besar -->
                                        <Avatar class="h-28 w-28 shadow-md ring-3 ring-primary/90 ring-offset-2 ring-offset-background">
                                            <AvatarImage
                                                v-if="userFotoUrl"
                                                :src="userFotoUrl"
                                                :alt="authUser?.nama ?? 'Foto'"
                                                class="h-full w-full object-cover object-top"
                                            />
                                            <AvatarFallback class="bg-muted text-muted-foreground">
                                                <img
                                                    :src="media + 'img/avatar/default.png'"
                                                    alt="Default Foto"
                                                    class="h-full w-full object-cover object-top"
                                                />
                                            </AvatarFallback>
                                        </Avatar>

                                        <!-- Nama -->
                                        <div class="flex flex-col items-center">
                                            <p class="-mt-2 text-center text-base leading-tight font-bold text-foreground">
                                                {{ authUser?.nama ?? '-' }}
                                            </p>
                                            <a
                                                href="#"
                                                @click.prevent="passwordModalOpen = true"
                                                class="mt-1 flex cursor-pointer items-center gap-1 text-[11px] font-medium text-primary hover:underline"
                                            >
                                                <KeyRound class="h-3 w-3" />
                                                Ubah Password
                                            </a>
                                        </div>

                                        <!-- Detail fields -->
                                        <div class="w-full space-y-3">
                                            <div class="space-y-0.5">
                                                <p class="text-[10px] font-semibold tracking-wide text-muted-foreground uppercase">NIP</p>
                                                <p class="text-sm font-medium text-foreground">{{ authUser?.nip ?? '-' }}</p>
                                            </div>
                                            <div class="space-y-0.5">
                                                <p class="text-[10px] font-semibold tracking-wide text-muted-foreground uppercase">NIP Baru</p>
                                                <p class="text-sm font-medium text-foreground">{{ authUser?.nip_baru ?? '-' }}</p>
                                            </div>
                                            <div class="space-y-0.5">
                                                <p class="text-[10px] font-semibold tracking-wide text-muted-foreground uppercase">Email BPS</p>
                                                <p class="text-sm font-medium text-foreground">{{ authUser?.email_bps ?? '-' }}</p>
                                            </div>
                                            <div class="space-y-0.5">
                                                <p
                                                    class="flex items-center gap-1 text-[10px] font-semibold tracking-wide text-muted-foreground uppercase"
                                                >
                                                    Gmail (<button
                                                        @click.prevent="emailModalOpen = true"
                                                        class="flex cursor-pointer items-center gap-0.5 font-medium text-primary hover:underline"
                                                    >
                                                        <Pencil class="h-2.5 w-2.5" />
                                                        Ganti Gmail</button
                                                    >)
                                                </p>
                                                <p class="text-sm font-medium text-foreground">{{ authUser?.email_gmail ?? '-' }}</p>
                                            </div>

                                            <!-- Role di modal -->
                                            <div v-if="authRoles.length" class="space-y-1.5">
                                                <p class="text-[10px] font-semibold tracking-wide text-muted-foreground uppercase">Role</p>
                                                <div class="flex flex-wrap gap-1.5">
                                                    <span
                                                        v-for="role in authRoles"
                                                        :key="role"
                                                        class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary capitalize"
                                                    >
                                                        {{ role }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </CardContent>
                </Card>

                <Card class="gap-0 rounded-xl border-border p-1.5 py-1.5">
                    <CardContent class="p-0">
                        <Link
                            v-for="item in desktopNavItems"
                            :key="item.key"
                            :href="item.href ?? '#'"
                            class="mb-1 flex items-center gap-2 rounded-lg px-2 py-2 text-xs font-medium transition last:mb-0"
                            :class="
                                item.active ? 'bg-primary text-primary-foreground shadow-sm' : 'text-foreground hover:bg-muted hover:text-foreground'
                            "
                        >
                            <component :is="item.icon ?? LayoutGrid" class="h-3.5 w-3.5 shrink-0" />
                            <span>{{ item.label }}</span>
                        </Link>
                    </CardContent>
                </Card>

                <Card class="gap-0 rounded-xl border-border py-0">
                    <CardHeader class="space-y-1 px-3 py-3 pb-2">
                        <CardTitle class="text-xs font-bold text-primary">Pengingat</CardTitle>
                        <p class="text-[11px] leading-4 text-muted-foreground">Ringkasan notifikasi dan reminder singkat.</p>
                    </CardHeader>
                    <CardContent class="space-y-2 px-3 pt-0 pb-3">
                        <Card
                            v-for="reminder in fixedReminderItems"
                            :key="reminder.key"
                            class="gap-1 rounded-lg border-border bg-muted p-2 py-2 shadow-none"
                        >
                            <p class="text-[11px] leading-4 font-semibold text-foreground">{{ reminder.title }}</p>
                            <p class="text-[10px] leading-4 text-muted-foreground">{{ reminder.description }}</p>
                        </Card>
                    </CardContent>
                </Card>
            </aside>

            <section class="min-w-0 rounded-2xl border border-border bg-card shadow-sm">
                <slot />
            </section>
        </main>

        <nav
            class="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-background/95 px-2 pt-2 pb-[max(env(safe-area-inset-bottom),0.5rem)] shadow-lg backdrop-blur lg:hidden"
        >
            <div class="grid grid-cols-4 gap-1">
                <Button
                    v-for="item in mobileNavItems"
                    :key="`mobile-${item.key}`"
                    as-child
                    variant="ghost"
                    class="h-auto min-h-14 rounded-xl px-1 py-2"
                >
                    <Link
                        :href="item.href ?? '#'"
                        class="flex flex-col items-center justify-center text-[11px] font-medium transition"
                        :class="item.active ? 'text-primary' : 'text-muted-foreground hover:text-foreground'"
                    >
                        <component :is="item.icon ?? LayoutGrid" class="mb-1 h-4 w-4" />
                        <span class="truncate">{{ item.label }}</span>
                    </Link>
                </Button>
            </div>
        </nav>

        <div class="h-24 lg:hidden" />
        <UpdateMyPasswordDialog v-model:open="passwordModalOpen" />
        <UpdateMyEmailDialog v-model:open="emailModalOpen" :current-email="authUser?.email_gmail ?? null" />
    </div>
</template>
