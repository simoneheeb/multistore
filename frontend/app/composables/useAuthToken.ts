import { useCookie } from 'nuxt/app'

/**
 * The API token, stored in a cookie rather than localStorage.
 *
 * A cookie is the only store readable during SSR, which is what lets the
 * server render admin pages for a signed-in visitor instead of flashing the
 * logged-out shell first.
 */
export function useAuthToken() {
    return useCookie<string | null>('storefront_token', {
        maxAge: 60 * 60 * 24 * 7,
        sameSite: 'lax',
        // Sent over https only in production; local development is plain http.
        secure: import.meta.env.PROD,
        path: '/',
    })
}
