import { defineNuxtRouteMiddleware, navigateTo } from 'nuxt/app'
import { useAuthStore } from '~~/modules/client/store/authStore'

/**
 * Route guard for the admin area.
 *
 * Runs on the server too: the token lives in a cookie, so an unauthenticated
 * request to /admin is redirected before any admin markup is rendered rather
 * than flashing the panel and bouncing after hydration.
 */
export default defineNuxtRouteMiddleware(async (to) => {
    const isAdminRoute = to.path.startsWith('/admin')
    const isLoginRoute = to.path === '/auth/login'

    if (!isAdminRoute && !isLoginRoute) return

    const auth = useAuthStore()
    const token = useAuthToken().value

    if (isAdminRoute) {
        if (!token) {
            // Remember where they were headed so the login can return them.
            return navigateTo({ path: '/auth/login', query: { redirect: to.fullPath } })
        }

        // Confirm the token still belongs to an admin; a revoked or demoted
        // account must not keep its access just because the cookie survives.
        await auth.fetchUser()

        if (!auth.user) {
            auth.logoutLocal()
            return navigateTo({ path: '/auth/login', query: { redirect: to.fullPath } })
        }

        if (!auth.user.isAdmin) {
            auth.logoutLocal()
            return navigateTo('/')
        }
    }

    // An already-signed-in admin has no reason to see the login screen.
    if (isLoginRoute && token) {
        await auth.fetchUser()
        if (auth.user?.isAdmin) return navigateTo('/admin')
    }
})
