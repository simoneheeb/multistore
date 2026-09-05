import { useCookie, useState } from 'nuxt/app'
import { computed } from 'vue'

export type ThemeMode = 'light' | 'dark'

/**
 * Light/dark switching.
 *
 * The choice lives in a cookie so the server can stamp `data-theme` on <html>
 * during SSR - without that, a dark-mode visitor gets a white flash on every
 * navigation before hydration catches up.
 */
export function useTheme() {
    const cookie = useCookie<ThemeMode>('storefront_theme', {
        maxAge: 60 * 60 * 24 * 365,
        sameSite: 'lax',
        path: '/',
    })

    const mode = useState<ThemeMode>('theme-mode', () => cookie.value || 'light')

    const isDark = computed(() => mode.value === 'dark')

    const apply = (next: ThemeMode) => {
        mode.value = next
        cookie.value = next

        if (import.meta.client) {
            document.documentElement.setAttribute('data-theme', next)
        }
    }

    const toggle = () => apply(isDark.value ? 'light' : 'dark')

    return { mode, isDark, toggle, apply }
}
