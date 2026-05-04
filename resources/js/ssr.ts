import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createSSRApp, Fragment, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import ScrollToTopButton from '@/components/common/ScrollToTopButton.vue';
import { Toaster } from '@/components/ui/sonner';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => (title ? `${title} - ${appName}` : appName),
            resolve: resolvePage,
            setup: ({ App, props, plugin }) =>
                createSSRApp({
                    render: () =>
                        h(Fragment, [
                            h(App, props),
                            h(Toaster, {
                                richColors: true,
                                position: 'top-right',
                                closeButton: false,
                                duration: 2000,
                            }),
                            h(ScrollToTopButton),
                        ]),
                }).use(plugin),
        }),
    { cluster: true },
);

function resolvePage(name: string) {
    const appPages = import.meta.glob<DefineComponent>('./pages/**/*.vue');
    const modulePages = import.meta.glob<DefineComponent>('../../Modules/**/resources/js/pages/**/*.vue');

    if (!name.includes('::')) {
        return resolvePageComponent<DefineComponent>(`./pages/${name}.vue`, appPages);
    }

    return resolveModulePage(name, modulePages);
}

function resolveModulePage(name: string, modulePages: Record<string, () => Promise<DefineComponent>>) {
    const [moduleAlias, pageName] = name.split('::');
    const moduleAliasLower = moduleAlias.toLowerCase();
    const pageFile = `${pageName}.vue`;

    const match = Object.keys(modulePages).find((path) => {
        const normalized = path.replace(/\\/g, '/');
        const parts = normalized.split('/');
        const modulesIndex = parts.lastIndexOf('Modules');
        if (modulesIndex === -1 || modulesIndex + 1 >= parts.length) {
            return false;
        }

        const moduleName = parts[modulesIndex + 1];

        return moduleName?.toLowerCase() === moduleAliasLower && normalized.endsWith(`/resources/js/pages/${pageFile}`);
    });

    if (!match) {
        throw new Error(`Module page not found: ${name}`);
    }

    return resolvePageComponent<DefineComponent>(match, modulePages);
}
