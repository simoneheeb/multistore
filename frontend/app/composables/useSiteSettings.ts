import { useAsyncData } from 'nuxt/app'
import { computed } from 'vue'
import { useApi } from '~/composables/useApi'
import type { SiteSettings } from '~/types/settings'

/**
 * Site settings, fetched once per request and shared by every component.
 *
 * useAsyncData with a fixed key means the payload is fetched on the server,
 * serialised into the page, and reused on the client - the header, footer and
 * each landing section all read the same object without refetching.
 */
export function useSiteSettings() {
    const { data, refresh, pending } = useAsyncData(
        'site-settings',
        async () => {
            const response = await useApi<SiteSettings>('/settings', { notifyOnError: false })
            return (response?.data ?? null) as SiteSettings | null
        },
        // Settings change rarely; re-fetching on every route change would add
        // a request per navigation for data that is already in memory.
        { server: true, lazy: false },
    )

    const settings = computed(() => data.value)

    /** Dot-path reader with a default, e.g. get('footer.contact.email', ''). */
    const get = <T>(path: string, fallback: T): T => {
        const value = path
            .split('.')
            .reduce<any>((carry, key) => (carry == null ? undefined : carry[key]), settings.value)

        return (value ?? fallback) as T
    }

    return { settings, get, refresh, pending }
}
