<script setup lang="ts">
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, House } from 'lucide-vue-next';
import { computed } from 'vue';

const backItem = computed(() => {
    if (props.breadcrumbs && props.breadcrumbs.length > 1) {
        return props.breadcrumbs[props.breadcrumbs.length - 2];
    }
    return null;
});

const handleBackClick = (e: MouseEvent) => {
    if (backItem.value?.onClick) {
        e.preventDefault();
        backItem.value.onClick();
    }
};

interface ModuleShellBreadcrumbItem {
    label: string;
    href?: string;
    onClick?: () => void;
}

const props = withDefaults(
    defineProps<{
        breadcrumbs?: ModuleShellBreadcrumbItem[];
        bodyVariant?: 'hub' | 'page';
        bodyClass?: string;
    }>(),
    {
        breadcrumbs: () => [{ label: 'Home', href: '/app' }],
        bodyVariant: 'page',
        bodyClass: undefined,
    },
);

const resolvedBodyClass = computed(() => {
    if (props.bodyClass) {
        return props.bodyClass;
    }

    if (props.bodyVariant === 'hub') {
        return 'space-y-4 px-4 py-4 sm:px-6 sm:py-5';
    }

    return 'mx-auto max-w-7xl space-y-4 px-3 py-4 sm:space-y-5 sm:px-4 sm:py-6';
});
</script>

<template>
    <div>
        <div class="border-b border-border px-4 py-3 sm:px-6">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center">
                    <Button
                        as-child
                        variant="outline"
                        size="icon"
                        class="-ml-2 h-8 w-8 border-primary/20 text-primary transition-all hover:bg-primary/10 active:scale-95"
                        :title="backItem ? 'Kembali' : 'Beranda'"
                    >
                        <Link
                            v-if="backItem && backItem.href"
                            :href="backItem.href"
                            @click="handleBackClick"
                        >
                            <ArrowLeft class="size-4" />
                        </Link>
                        <button
                            v-else-if="backItem"
                            type="button"
                            @click="handleBackClick"
                        >
                            <ArrowLeft class="size-4" />
                        </button>
                        <Link
                            v-else
                            href="/app"
                        >
                            <House class="size-4" />
                        </Link>
                    </Button>
                </div>

                <Breadcrumb>
                    <BreadcrumbList class="flex-wrap gap-1.5 sm:gap-2">
                        <template v-for="(crumb, index) in breadcrumbs" :key="`${crumb.label}-${index}`">
                            <BreadcrumbItem>
                                <BreadcrumbLink v-if="crumb.href" as-child>
                                    <Link
                                        :href="crumb.href"
                                        class="flex items-center gap-1.5"
                                        :class="{ 'font-bold text-foreground': index === breadcrumbs.length - 1 }"
                                    >
                                        {{ crumb.label }}
                                    </Link>
                                </BreadcrumbLink>
                                <button
                                    v-else-if="crumb.onClick"
                                    type="button"
                                    class="flex cursor-pointer items-center gap-1.5 text-sm text-muted-foreground transition hover:text-foreground"
                                    :class="{ 'font-bold text-foreground': index === breadcrumbs.length - 1 }"
                                    @click="crumb.onClick"
                                >
                                    {{ crumb.label }}
                                </button>
                                <BreadcrumbPage
                                    v-else
                                    class="flex items-center gap-1.5"
                                    :class="{ 'font-bold text-foreground': index === breadcrumbs.length - 1 }"
                                >
                                    {{ crumb.label }}
                                </BreadcrumbPage>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1" />
                        </template>
                    </BreadcrumbList>
                </Breadcrumb>
            </div>
        </div>

        <div :class="resolvedBodyClass">
            <slot />
        </div>
    </div>
</template>
