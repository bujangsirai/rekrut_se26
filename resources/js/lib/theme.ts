import { ref } from 'vue';

export type ThemeMode = 'light' | 'dark';

const themeStorageKey = 'umum-theme';
const isDark = ref(false);
let isInitialized = false;

export function applyTheme(mode: ThemeMode): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.classList.toggle('dark', mode === 'dark');
    localStorage.setItem(themeStorageKey, mode);
    isDark.value = mode === 'dark';
}

export function initializeTheme(defaultMode: ThemeMode = 'light'): void {
    if (isInitialized) {
        return;
    }

    const savedTheme = localStorage.getItem(themeStorageKey);
    if (savedTheme === 'light' || savedTheme === 'dark') {
        applyTheme(savedTheme);
        isInitialized = true;
        return;
    }

    applyTheme(defaultMode);
    isInitialized = true;
}

export function toggleTheme(): void {
    applyTheme(isDark.value ? 'light' : 'dark');
}

export function useTheme() {
    return {
        isDark,
        applyTheme,
        initializeTheme,
        toggleTheme,
    };
}
