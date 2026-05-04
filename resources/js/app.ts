import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, Fragment, h } from 'vue';
import FlashMessageListener from '@/components/common/FlashMessageListener.vue';
import ScrollToTopButton from '@/components/common/ScrollToTopButton.vue';
import { Toaster } from '@/components/ui/sonner';
import { autoSubscribePushForAuthenticatedUser, cleanupPushSubscriptionBinding } from '@/lib/push-auto-subscribe';
import { markInstalled, setDeferredInstallPrompt, type BeforeInstallPromptEvent } from '@/lib/pwa-install';
import { initializeTheme } from '@/lib/theme';
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

    const anchor = event.target.closest<HTMLAnchorElement>('a[href="/logout"]');

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
    return resolvePageComponent<DefineComponent>(`./pages/${name}.vue`, appPages);
}
