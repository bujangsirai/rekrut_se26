import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { Component, DefineComponent } from 'vue';
import { createApp, Fragment, h } from 'vue';
import FlashMessageListener from '@/components/common/FlashMessageListener.vue';
import ScrollToTopButton from '@/components/common/ScrollToTopButton.vue';
import { Toaster } from '@/components/ui/sonner';
import { autoSubscribePushForAuthenticatedUser, cleanupPushSubscriptionBinding } from '@/lib/push-auto-subscribe';
import { markInstalled, setDeferredInstallPrompt, type BeforeInstallPromptEvent } from '@/lib/pwa-install';
import { initializeTheme } from '@/lib/theme';
import SharedModuleLayout from '../../Modules/Shared/resources/js/components/layouts/SharedModuleLayout.vue';
import '../css/app.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
initializeTheme('light');

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-expect-error
    resolve: (name) => resolvePage(name),
    setup({ el, App, props, plugin }) {
        createApp({
            render: () =>
                h(Fragment, [
                    h(App, props),
                    h(FlashMessageListener),
                    h(Toaster, {
                        richColors: true,
                        position: 'top-right',
                        closeButton: false,
                        duration: 5000,
                    }),
                    h(ScrollToTopButton),
                ]),
        })
            .use(plugin)
            .mount(el);

        void autoSubscribePushForAuthenticatedUser(props.initialPage.props);
    },
    progress: {
        color: '#4B5563',
    },
});

router.on('success', (event) => {
    void autoSubscribePushForAuthenticatedUser(event.detail.page.props);
});

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    setDeferredInstallPrompt(event as BeforeInstallPromptEvent);
});

window.addEventListener('appinstalled', () => {
    markInstalled();
});

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const anchor = event.target.closest<HTMLAnchorElement>('a[href="/admin/logout"]');

    if (!anchor || event.defaultPrevented) {
        return;
    }

    const mouseEvent = event as MouseEvent;

    if (
        mouseEvent.button !== 0
        || mouseEvent.metaKey
        || mouseEvent.ctrlKey
        || mouseEvent.shiftKey
        || mouseEvent.altKey
    ) {
        return;
    }

    event.preventDefault();

    void cleanupPushSubscriptionBinding().finally(() => {
        window.location.assign(anchor.href);
    });
});

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

    return resolvePageComponent(match, modulePages).then((page) => applyDefaultModuleLayout(name, page));
}

function applyDefaultModuleLayout(name: string, page: unknown): unknown {
    const [moduleAlias] = name.split('::');
    if (!moduleAlias) {
        return page;
    }

    const pageModule = page as { default?: Component;[key: string]: unknown };
    const component = (pageModule.default ?? page) as DefineComponent & { layout?: unknown };

    if ('layout' in component) {
        return page;
    }

    if (Object.isExtensible(component)) {
        component.layout = SharedModuleLayout;
        return page;
    }

    const wrappedComponent = Object.create(component) as DefineComponent & { layout?: unknown };
    wrappedComponent.layout = SharedModuleLayout;
    if (pageModule.default) {
        pageModule.default = wrappedComponent;
        return pageModule;
    }

    return wrappedComponent;
}
