export type BeforeInstallPromptEvent = Event & {
    prompt: () => Promise<void>;
    userChoice: Promise<{
        outcome: 'accepted' | 'dismissed';
        platform: string;
    }>;
};

type Listener = () => void;

let deferredPrompt: BeforeInstallPromptEvent | null = null;
let installed = false;
const listeners = new Set<Listener>();

function notify(): void {
    for (const listener of listeners) {
        listener();
    }
}

export function setDeferredInstallPrompt(event: BeforeInstallPromptEvent | null): void {
    deferredPrompt = event;
    notify();
}

export function markInstalled(): void {
    installed = true;
    deferredPrompt = null;
    notify();
}

export function canInstallPwa(): boolean {
    return deferredPrompt !== null && !installed;
}

export function getDeferredInstallPrompt(): BeforeInstallPromptEvent | null {
    return deferredPrompt;
}

export function isInstalled(): boolean {
    if (installed) {
        return true;
    }

    return window.matchMedia('(display-mode: standalone)').matches;
}

export function isIosDevice(): boolean {
    return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
}

export function subscribeInstallState(listener: Listener): () => void {
    listeners.add(listener);

    return () => {
        listeners.delete(listener);
    };
}
