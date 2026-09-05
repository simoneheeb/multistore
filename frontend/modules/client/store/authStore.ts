import { defineStore } from 'pinia'
import { navigateTo } from 'nuxt/app'
import { AuthService } from '../services/AuthService'
import type { AuthCredentials, AuthUser } from '~/types/interfaces'

/**
 * Admin session.
 *
 * The token itself lives in a cookie (see useAuthToken) rather than in the
 * store, so it survives a reload and is readable during SSR. The store only
 * holds the decoded user and the request state around signing in and out.
 */
export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as AuthUser | null,
        loading: false,
        error: null as string | null,
    }),

    getters: {
        isLoggedIn: () => Boolean(useAuthToken().value),
        isAdmin: state => Boolean(state.user?.isAdmin),
    },

    actions: {
        async login(payload: AuthCredentials): Promise<boolean> {
            this.loading = true
            this.error = null

            try {
                const response: any = await AuthService.login(payload)

                // The API answers 200 with a token only on success; anything
                // else has already surfaced its message through useApi.
                if (!response?.token) {
                    this.error = response?.message ?? 'Sign-in failed.'
                    return false
                }

                useAuthToken().value = response.token
                this.user = response.user ?? null

                if (!this.user?.isAdmin) {
                    // A non-admin account has no destination in this app, so
                    // the session is dropped rather than left half-signed-in.
                    this.logoutLocal()
                    this.error = 'This account does not have admin access.'
                    return false
                }

                await navigateTo('/admin')
                return true
            } finally {
                this.loading = false
            }
        },

        /** Re-hydrates the user from an existing cookie token. */
        async fetchUser(): Promise<void> {
            if (!useAuthToken().value || this.user) return

            this.user = await AuthService.me()
        },

        async logout(): Promise<void> {
            await AuthService.logout()
            this.logoutLocal()
            await navigateTo('/auth/login')
        },

        /** Clears local session state without calling the API. */
        logoutLocal(): void {
            useAuthToken().value = null
            this.user = null
        },
    },
})
