import { defineNuxtModule } from '@nuxt/kit'
import type { Nuxt, NuxtPage } from 'nuxt/schema'
import { join, resolve } from 'path'

/**
 * Admin panel module.
 *
 * Every route here renders in the "dashboard" layout and is gated by the
 * global auth middleware, which checks both the token and the admin flag.
 */
export default defineNuxtModule({
    meta: {
        name: 'admin',
        configKey: 'dashboard',
    },

    setup(_options, nuxt: Nuxt) {
        nuxt.hook('components:dirs', (dirs: any) => {
            dirs.push({ path: join(__dirname, 'components'), pathPrefix: false })
        })

        nuxt.hook('imports:dirs', (dirs: string[]) => {
            dirs.push(resolve(__dirname, 'composables'))
        })

        nuxt.hook('pages:extend', (pages) => {
            const routes: NuxtPage[] = [
                {
                    name: 'admin',
                    path: '/admin',
                    file: resolve(__dirname, 'pages/index.vue'),
                    meta: { layout: 'dashboard' },
                },
                {
                    name: 'admin-brands',
                    path: '/admin/brands',
                    file: resolve(__dirname, 'pages/brands/index.vue'),
                    meta: { layout: 'dashboard' },
                },
                {
                    name: 'admin-categories',
                    path: '/admin/categories',
                    file: resolve(__dirname, 'pages/categories/index.vue'),
                    meta: { layout: 'dashboard' },
                },
                {
                    name: 'admin-products',
                    path: '/admin/products',
                    file: resolve(__dirname, 'pages/products/index.vue'),
                    meta: { layout: 'dashboard' },
                },
                {
                    name: 'admin-settings',
                    path: '/admin/settings',
                    file: resolve(__dirname, 'pages/settings/index.vue'),
                    meta: { layout: 'dashboard' },
                },
            ]

            pages.push(...routes)
        })
    },
})
