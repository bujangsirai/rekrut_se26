type AuthUser = {
    id?: number | string;
};

type InertiaLikePageProps = {
    auth?: {
        user?: AuthUser | null;
    };
};

let lastSyncedSignature: string | null = null;
let isSyncInFlight = false;
let isCleanupInFlight = false;
const boundEndpointStorageKey = 'push:last-bound-endpoint';
const boundUserStorageKey = 'push:last-bound-user';

function getCsrfToken(): string | null {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? null;
}

function getVapidPublicKey(): string | null {
    return document.querySelector('meta[name="vapid-public-key"]')?.getAttribute('content') ?? null;
}

function isAuthenticated(pageProps: unknown): pageProps is InertiaLikePageProps {
    if (!pageProps || typeof pageProps !== 'object') {
        return false;
    }

    const maybeProps = pageProps as InertiaLikePageProps;

    return Boolean(maybeProps.auth?.user);
}

function resolveUserId(pageProps: InertiaLikePageProps): string {
    const user = pageProps.auth?.user;
    if (!user) {
        return 'guest';
    }

    return String(user.id ?? 'auth-user');
}

type PushSubscriptionPayload = {
    endpoint: string;
    public_key: string | null;
    auth_token: string | null;
    encoding: string;
};

function urlBase64ToUint8Array(base64String: string): Uint8Array {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let index = 0; index < rawData.length; index += 1) {
        outputArray[index] = rawData.charCodeAt(index);
    }

    return outputArray;
}

function encodeSubscriptionKey(key: ArrayBuffer | null): string | null {
    if (!key) {
        return null;
    }

    return window.btoa(String.fromCharCode(...new Uint8Array(key)));
}

function buildSubscriptionPayload(subscription: PushSubscription): PushSubscriptionPayload {
    return {
        endpoint: subscription.endpoint,
        public_key: encodeSubscriptionKey(subscription.getKey('p256dh') as ArrayBuffer | null),
        auth_token: encodeSubscriptionKey(subscription.getKey('auth') as ArrayBuffer | null),
        encoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
    };
}

async function ensureServiceWorkerRegistration(): Promise<ServiceWorkerRegistration | null> {
    if (!('serviceWorker' in navigator)) {
        return null;
    }

    const existingRegistration = await navigator.serviceWorker.getRegistration('/sw.js');
    if (existingRegistration) {
        return existingRegistration;
    }

    await navigator.serviceWorker.register('/sw.js');
    return navigator.serviceWorker.ready;
}

async function ensureNotificationPermission(): Promise<boolean> {
    if (!('Notification' in window)) {
        return false;
    }

    if (Notification.permission === 'granted') {
        return true;
    }

    if (Notification.permission === 'denied') {
        return false;
    }

    const permission = await Notification.requestPermission();
    return permission === 'granted';
}

async function ensureBrowserSubscription(registration: ServiceWorkerRegistration): Promise<PushSubscription | null> {
    const existingSubscription = await registration.pushManager.getSubscription();
    if (existingSubscription) {
        return existingSubscription;
    }

    const vapidPublicKey = getVapidPublicKey();
    if (!vapidPublicKey) {
        return null;
    }

    return registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey) as Uint8Array<ArrayBuffer>,
    });
}

async function syncSubscriptionToBackend(subscription: PushSubscription): Promise<void> {
    const csrfToken = getCsrfToken();
    if (!csrfToken) {
        return;
    }

    const body = buildSubscriptionPayload(subscription);

    const response = await fetch('/notifications/subscribe', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(body),
    });

    if (!response.ok) {
        throw new Error(`Push subscribe request failed with status ${response.status}`);
    }
}

async function unbindSubscriptionFromBackend(subscription: PushSubscription): Promise<void> {
    const csrfToken = getCsrfToken();
    if (!csrfToken) {
        return;
    }

    const response = await fetch('/notifications/unbind', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(buildSubscriptionPayload(subscription)),
    });

    if (!response.ok) {
        throw new Error(`Push unbind request failed with status ${response.status}`);
    }
}

function rememberBoundSubscription(userId: string, subscription: PushSubscription): void {
    window.localStorage.setItem(boundUserStorageKey, userId);
    window.localStorage.setItem(boundEndpointStorageKey, subscription.endpoint);
}

function clearRememberedBoundSubscription(): void {
    window.localStorage.removeItem(boundUserStorageKey);
    window.localStorage.removeItem(boundEndpointStorageKey);
}

async function getExistingBrowserSubscription(): Promise<PushSubscription | null> {
    const registration = await ensureServiceWorkerRegistration();

    if (!registration) {
        return null;
    }

    return registration.pushManager.getSubscription();
}

export async function cleanupPushSubscriptionBinding(): Promise<void> {
    if (isCleanupInFlight) {
        return;
    }

    const rememberedEndpoint = window.localStorage.getItem(boundEndpointStorageKey);

    if (!rememberedEndpoint) {
        lastSyncedSignature = null;
        return;
    }

    isCleanupInFlight = true;

    try {
        const subscription = await getExistingBrowserSubscription();

        if (!subscription || subscription.endpoint !== rememberedEndpoint) {
            clearRememberedBoundSubscription();
            lastSyncedSignature = null;
            return;
        }

        await unbindSubscriptionFromBackend(subscription);
        clearRememberedBoundSubscription();
        lastSyncedSignature = null;
    } catch (error) {
        console.error('Push binding cleanup failed.', error);
    } finally {
        isCleanupInFlight = false;
    }
}

export async function autoSubscribePushForAuthenticatedUser(pageProps: unknown): Promise<void> {
    if (!isAuthenticated(pageProps)) {
        await cleanupPushSubscriptionBinding();
        return;
    }

    if (isSyncInFlight) {
        return;
    }

    isSyncInFlight = true;

    try {
        const userId = resolveUserId(pageProps);
        const registration = await ensureServiceWorkerRegistration();
        if (!registration) {
            return;
        }

        const hasPermission = await ensureNotificationPermission();
        if (!hasPermission) {
            return;
        }

        const subscription = await ensureBrowserSubscription(registration);
        if (!subscription) {
            return;
        }

        const syncSignature = `${userId}:${subscription.endpoint}`;
        if (lastSyncedSignature === syncSignature) {
            return;
        }

        await syncSubscriptionToBackend(subscription);
        rememberBoundSubscription(userId, subscription);
        lastSyncedSignature = syncSignature;
    } catch (error) {
        console.error('Auto push subscribe failed.', error);
    } finally {
        isSyncInFlight = false;
    }
}
