import { defineNuxtModule } from '@nuxt/kit'
import type { Nuxt, NuxtPage } from 'nuxt/schema'
import { join, resolve } from 'path'

/**
 * Public storefront module.
 *
 * Pages are registered explicitly rather than through file-based routing so
 * each route's name, path and nesting is declared in one place - which is
 * what lets the category route accept an optional second segment.
 */
export default defineNuxtModule({
    meta: {
        name: 'client',
        configKey: 'storefront',
    },

    setup(_options, nuxt: Nuxt) {
        // Auto-register this module's components without a directory prefix,
        // so home/HomeHero.vue is <HomeHero />.
        nuxt.hook('components:dirs', (dirs: any) => {
            dirs.push({ path: join(__dirname, 'components'), pathPrefix: false })
        })

        nuxt.hook('imports:dirs', (dirs: string[]) => {
            dirs.push(resolve(__dirname, 'composables'))
        })

        nuxt.hook('pages:extend', (pages) => {
            const routes: NuxtPage[] = [
                {
                    name: 'index',
                    path: '/',
                    file: resolve(__dirname, 'pages/index.vue'),
                },
                {
                    name: 'about',
                    path: '/about',
                    file: resolve(__dirname, 'pages/about.vue'),
                },
                {
                    name: 'contact',
                    path: '/contact',
                    file: resolve(__dirname, 'pages/contact.vue'),
                },

                // Products
                {
                    name: 'products',
                    path: '/products',
                    file: resolve(__dirname, 'pages/products/index.vue'),
                },
                {
                    name: 'product-detail',
                    path: '/products/:slug',
                    file: resolve(__dirname, 'pages/products/[slug].vue'),
                },

                // Categories. The optional :childSlug is what makes
                // /categories/balls/football resolve a nested node.
                {
                    name: 'categories',
                    path: '/categories',
                    file: resolve(__dirname, 'pages/categories/index.vue'),
                },
                {
                    name: 'category-detail',
                    path: '/categories/:slug/:childSlug?',
                    file: resolve(__dirname, 'pages/categories/[slug].vue'),
                },

                // Brands
                {
                    name: 'brands',
                    path: '/brands',
                    file: resolve(__dirname, 'pages/brands/index.vue'),
                },
                {
                    name: 'brand-detail',
                    path: '/brands/:slug',
                    file: resolve(__dirname, 'pages/brands/[slug].vue'),
                },

                // Auth
                {
                    name: 'auth-login',
                    path: '/auth/login',
                    file: resolve(__dirname, 'pages/auth/login.vue'),
                    meta: { layout: 'auth' },
                },
            ]

            pages.push(...routes)
        })
    },
})
