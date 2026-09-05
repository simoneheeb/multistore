import { useApi } from '~/composables/useApi'
import type { AuthCredentials, AuthUser } from '~/types/interfaces'

export const AuthService = {
    async login(payload: AuthCredentials) {
        return await useApi<unknown>('/auth/login', {
            method: 'POST',
            body: payload,
        })
    },

    async logout() {
        return await useApi('/auth/logout', { method: 'POST', notifyOnError: false })
    },

    /** Restores the signed-in account from the stored token. */
    async me() {
        const response = await useApi<unknown>('/auth/me', { notifyOnError: false })
        return ((response as any)?.user ?? null) as AuthUser | null
    },
}
