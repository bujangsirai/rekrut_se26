<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Globe2,
    LayoutGrid,
    LogOut,
    Menu,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed, ref, watch } from 'vue';
import adminNavigation from '@/config/module-navigation.admin.json';
import sharedNavigation from '@/config/module-navigation.shared.json';
import {
    filterPagesByRoles,
    hasRoleAccess,
    type ModuleNavigationConfig,
    type ModuleNavigationPageItem,
} from '@/lib/module-navigation';

interface AuthUser {
    username?: string | null;
    nama?: string | null;
}

interface SidebarModule {
    key: string;
    title: string;
    icon: Component;
    pages: ModuleNavigationPageItem[];
}

const page = usePage();

const isSidebarCollapsed = ref(false);
const isMobileSidebarOpen = ref(false);
const selectedModuleKey = ref<string | null>(null);

const authUser = computed<AuthUser | null>(() => ((page.props.auth as { user?: AuthUser | null })?.user ?? null));
const authRoles = computed<string[]>(() => ((page.props.auth as { roles?: string[] })?.roles ?? []));
const moduleStatuses = computed<Record<string, boolean>>(() => ((page.props.modules as { statuses?: Record<string, boolean> })?.statuses ?? {}));

const currentPath = computed(() => {
    const [path] = page.url.split(/[?#]/);
    return path || '/';
});

const moduleConfigs: ModuleNavigationConfig[] = [adminNavigation as ModuleNavigationConfig, sharedNavigation as ModuleNavigationConfig];

const modules = computed<SidebarModule[]>(() =>
    moduleConfigs
        .filter((config) => moduleStatuses.value[config.module.name] ?? true)
        .filter((config) => hasRoleAccess(config.module.roles, authRoles.value))
        .map((config) => {
            const moduleKey = config.module.key;
            const roleFilteredPages = filterPagesByRoles((config.pages ?? []).filter((item) => item.level === 2), authRoles.value);

            const pages =
                moduleKey === 'admin'
                    ? [
                        {
                            key: 'dashboard',
                            title: 'Dashboard',
                            level: 2,
                            href: '/admin/dashboard',
                            componentKey: 'admin.dashboard',
                            description: 'Ringkasan utama admin.',
                        },
                        ...roleFilteredPages,
                    ]
                    : roleFilteredPages;

            const icon = moduleKey === 'admin' ? ShieldCheck : Globe2;

            return {
                key: moduleKey,
                title: config.module.title ?? config.module.name,
                icon,
                pages,
            };
        })
        .filter((moduleItem) => moduleItem.pages.length > 0),
);

const selectedModule = computed(() => {
    if (!selectedModuleKey.value) {
        return modules.value[0] ?? null;
    }

    return modules.value.find((item) => item.key === selectedModuleKey.value) ?? modules.value[0] ?? null;
});

watch(
    modules,
    (items) => {
        if (!items.length) {
            selectedModuleKey.value = null;
            return;
        }

        if (!selectedModuleKey.value || !items.some((item) => item.key === selectedModuleKey.value)) {
            selectedModuleKey.value = items[0]?.key ?? null;
        }
    },
    { immediate: true },
);

watch(
    currentPath,
    () => {
        const matchedModule = modules.value.find((moduleItem) =>
            moduleItem.pages.some((menuItem) => isInternalLink(menuItem.href) && isActivePath(currentPath.value, menuItem.href)),
        );

        if (matchedModule) {
            selectedModuleKey.value = matchedModule.key;
        }
    },
    { immediate: true },
);

function isExternalLink(href: string): boolean {
    return /^https?:\/\//i.test(href);
}

function isInternalLink(href: string): boolean {
    return !isExternalLink(href);
}

function normalizePath(path: string): string {
    return path.replace(/\/+$/, '') || '/';
}

function isActivePath(current: string, href: string): boolean {
    const currentNormalized = normalizePath(current);
    const hrefNormalized = normalizePath(href);

    if (hrefNormalized === '/') {
        return currentNormalized === '/';
    }

    return currentNormalized === hrefNormalized || currentNormalized.startsWith(`${hrefNormalized}/`);
}

function pageIconFor(menuKey: string): Component {
    if (menuKey.includes('user') || menuKey.includes('customer')) {
        return Users;
    }

    return LayoutGrid;
}

function closeMobileSidebar(): void {
    isMobileSidebarOpen.value = false;
}
</script>

<template>
    <div class="min-h-screen bg-[#f3f5fb] text-slate-800">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur md:hidden">
            <div class="flex items-center justify-between gap-3">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white"
                    @click="isMobileSidebarOpen = true"
                >
                    <Menu class="h-5 w-5 text-slate-700" />
                </button>
                <p class="truncate text-sm font-semibold text-slate-900">
                    {{ selectedModule?.title ?? 'Admin' }}
                </p>
                <span class="text-xs font-medium text-slate-500">{{ authUser?.username ?? authUser?.nama ?? 'User' }}</span>
            </div>
        </header>

        <div class="flex min-h-[calc(100vh-0px)]">
            <div
                v-if="isMobileSidebarOpen"
                class="fixed inset-0 z-40 bg-slate-900/40 md:hidden"
                @click="closeMobileSidebar"
            />

            <aside class="hidden w-20 flex-col border-r border-slate-200 bg-white md:flex">
                <div class="flex h-16 items-center justify-center border-b border-slate-200">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                        <ShieldCheck class="h-5 w-5" />
                    </div>
                </div>

                <nav class="flex flex-1 flex-col items-center gap-3 py-4">
                    <button
                        v-for="moduleItem in modules"
                        :key="moduleItem.key"
                        type="button"
                        class="flex h-11 w-11 items-center justify-center rounded-xl transition"
                        :class="
                            selectedModule?.key === moduleItem.key
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800'
                        "
                        @click="selectedModuleKey = moduleItem.key"
                    >
                        <component :is="moduleItem.icon" class="h-5 w-5" />
                    </button>
                </nav>
            </aside>

            <aside
                class="fixed top-0 left-0 z-50 h-full border-r border-slate-200 bg-white transition md:static md:z-auto"
                :class="[
                    isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
                    isSidebarCollapsed ? 'w-[86px]' : 'w-[280px]',
                ]"
            >
                <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4">
                    <p v-if="!isSidebarCollapsed" class="truncate text-sm font-bold text-slate-900">
                        {{ selectedModule?.title ?? 'Menu' }}
                    </p>
                    <div class="ml-auto flex items-center gap-2">
                        <button
                            type="button"
                            class="hidden h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 md:inline-flex"
                            @click="isSidebarCollapsed = !isSidebarCollapsed"
                        >
                            <ChevronLeft v-if="!isSidebarCollapsed" class="h-4 w-4" />
                            <ChevronRight v-else class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 md:hidden"
                            @click="closeMobileSidebar"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div class="space-y-4 p-3">
                    <div v-if="!isSidebarCollapsed" class="rounded-xl bg-slate-50 px-3 py-2 text-xs text-slate-500">
                        Login sebagai
                        <span class="font-semibold text-slate-700">{{ authUser?.username ?? authUser?.nama ?? '-' }}</span>
                    </div>

                    <nav class="space-y-1">
                        <template v-for="menuItem in selectedModule?.pages ?? []" :key="menuItem.key">
                            <Link
                                v-if="isInternalLink(menuItem.href)"
                                :href="menuItem.href"
                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition"
                                :class="
                                    isActivePath(currentPath, menuItem.href)
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                                "
                                @click="closeMobileSidebar"
                            >
                                <component :is="pageIconFor(menuItem.key)" class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">{{ menuItem.title }}</span>
                            </Link>

                            <a
                                v-else
                                :href="menuItem.href"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                            >
                                <component :is="pageIconFor(menuItem.key)" class="h-4 w-4 shrink-0" />
                                <span v-if="!isSidebarCollapsed" class="truncate">{{ menuItem.title }}</span>
                            </a>
                        </template>
                    </nav>
                </div>

                <div class="absolute right-0 bottom-0 left-0 border-t border-slate-200 p-3">
                    <Link
                        href="/admin/logout"
                        class="flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700"
                    >
                        <LogOut class="h-4 w-4" />
                        <span v-if="!isSidebarCollapsed">Logout</span>
                    </Link>
                </div>
            </aside>

            <main class="min-w-0 flex-1 p-4 md:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
