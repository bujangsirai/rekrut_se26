<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronLeft,
    ChevronRight,
    ClipboardList,
    Database,
    FileText,
    FolderSearch,
    Globe,
    Headphones,
    LayoutGrid,
    LogOut,
    Menu,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed, ref } from 'vue';
import navigationConfig from '@/config/module-navigation.json';
import { filterPagesByRoles, hasRoleAccess, type ModuleNavigationConfig, type ModuleNavigationPageItem } from '@/lib/module-navigation';

interface AuthUser {
    username?: string | null;
    nama?: string | null;
}

interface SidebarPageItem extends ModuleNavigationPageItem {
    section?: string;
}

const page = usePage();

const isSidebarCollapsed = ref(false);
const isMobileSidebarOpen = ref(false);
const authUser = computed<AuthUser | null>(() => (page.props.auth as { user?: AuthUser | null })?.user ?? null);
const authRoles = computed<string[]>(() => (page.props.auth as { roles?: string[] })?.roles ?? []);
const moduleStatuses = computed<Record<string, boolean>>(() => (page.props.modules as { statuses?: Record<string, boolean> })?.statuses ?? {});

const currentPath = computed(() => {
    const [path] = page.url.split(/[?#]/);
    return path || '/';
});

const navConfig = navigationConfig as ModuleNavigationConfig;
const moduleEnabled = computed<boolean>(() => {
    const moduleName = navConfig.module?.name;
    if (!moduleName) {
        return true;
    }

    return moduleStatuses.value[moduleName] ?? true;
});
const groupConfigs = computed(() => navConfig.groups ?? []);

const sidebarTitle = computed<string>(() => navConfig.module?.title ?? 'Rekrutmen SE 2026');

const menuPages = computed<SidebarPageItem[]>(() => {
    if (!moduleEnabled.value) {
        return [];
    }

    const roleFilteredPages = filterPagesByRoles(
        (navConfig.pages ?? []).filter((item) => item.level === 2),
        authRoles.value,
    ) as SidebarPageItem[];

    return roleFilteredPages;
});

const groupedMenuPages = computed<Array<{ section: string; sectionLabel: string; items: SidebarPageItem[] }>>(() => {
    const groupedMap = new Map<string, SidebarPageItem[]>();
    const groupConfigMap = new Map(groupConfigs.value.map((group) => [group.key, group]));

    for (const item of menuPages.value) {
        const section = item.section?.trim() || 'lainnya';
        const existingItems = groupedMap.get(section) ?? [];
        existingItems.push(item);
        groupedMap.set(section, existingItems);
    }

    return (
        Array.from(groupedMap.entries())
            .map(([section, items]) => {
                const groupConfig = groupConfigMap.get(section);
                if (groupConfig && !hasRoleAccess(groupConfig.roles, authRoles.value)) {
                    return null;
                }

                return {
                    section,
                    sectionLabel: groupConfig?.title ?? section,
                    order: groupConfig?.order ?? 999,
                    items,
                };
            })
            .filter((group) => group !== null && group.items.length > 0)
            //    @ts-ignore
            .sort((a, b) => a.order - b.order)
            //    @ts-ignore
            .map(({ section, sectionLabel, items }) => ({ section, sectionLabel, items }))
    );
});

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

function pageIconFor(iconName?: string): Component {
    const iconMap: Record<string, Component> = {
        users: Users,
        'shield-check': ShieldCheck,
        'clipboard-list': ClipboardList,
        globe: Globe,
        database: Database,
        'book-open': BookOpen,
        'file-text': FileText,
        headphones: Headphones,
        'folder-search': FolderSearch,
    };

    if (!iconName) {
        return LayoutGrid;
    }

    return iconMap[iconName] ?? LayoutGrid;
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
                    {{ sidebarTitle }}
                </p>
                <span class="text-xs font-medium text-slate-500">{{ authUser?.username ?? authUser?.nama ?? 'User' }}</span>
            </div>
        </header>

        <div class="flex min-h-[calc(100vh-0px)]">
            <div v-if="isMobileSidebarOpen" class="fixed inset-0 z-40 bg-slate-900/40 md:hidden" @click="closeMobileSidebar" />

            <aside
                class="fixed top-0 left-0 z-50 flex h-full flex-col self-start border-r border-slate-200 bg-white transition md:sticky md:top-0 md:z-auto md:h-screen"
                :class="[isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0', isSidebarCollapsed ? 'w-[72px]' : 'w-[240px]']"
            >
                <div class="flex h-16 items-center justify-between border-b border-slate-200 px-4">
                    <p v-if="!isSidebarCollapsed" class="truncate text-sm font-bold text-slate-900">
                        {{ sidebarTitle }}
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

                <div class="flex-1 overflow-y-auto p-3 pb-6">
                    <div v-if="!isSidebarCollapsed" class="rounded-xl bg-slate-50 px-3 py-2 text-xs text-slate-500">
                        <p class="text-sm text-slate-600">
                            Login sebagai
                            <span class="text-slate-500">
                                {{ authUser?.username ?? authUser?.nama ?? '-' }}
                            </span>
                            <span class="font-semibold text-slate-800"> ({{ authRoles[0] ?? '-' }}) </span>
                        </p>
                    </div>

                    <nav class="mt-4 space-y-3">
                        <section v-for="group in groupedMenuPages" :key="group.section" class="space-y-1">
                            <p v-if="!isSidebarCollapsed" class="px-3 pt-2 text-[11px] font-semibold tracking-wider text-slate-400 uppercase">
                                {{ group.sectionLabel }}
                            </p>
                            <template v-for="menuItem in group.items" :key="menuItem.key">
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
                                    <component :is="pageIconFor(menuItem.icon)" class="h-4 w-4 shrink-0" />
                                    <span v-if="!isSidebarCollapsed" class="truncate">{{ menuItem.title }}</span>
                                </Link>

                                <a
                                    v-else
                                    :href="menuItem.href"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                                >
                                    <component :is="pageIconFor(menuItem.icon)" class="h-4 w-4 shrink-0" />
                                    <span v-if="!isSidebarCollapsed" class="truncate">{{ menuItem.title }}</span>
                                </a>
                            </template>
                        </section>
                    </nav>
                </div>

                <div class="mt-auto border-t border-slate-200 p-3">
                    <Link
                        href="/logout"
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
