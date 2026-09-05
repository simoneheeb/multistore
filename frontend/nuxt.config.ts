import { defineNuxtConfig } from 'nuxt/config'
import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
    compatibilityDate: '2025-07-15',

    /**
     * Server-side rendering is required, not optional: the catalogue lives
     * behind an API and search engines must see the rendered markup, meta
     * tags and JSON-LD in the first response.
     */
    ssr: true,

    devtools: { enabled: true },

    runtimeConfig: {
        // Server-only.
        apiSecret: process.env.NUXT_API_SECRET ?? '',
        // Base URL used for server-side fetches. Falls back to the public
        // one so a single env var is enough in most deployments.
        apiBaseServer: process.env.NUXT_API_BASE_SERVER ?? '',

        public: {
            apiBase: 'http://herd_url.test/api',
            siteUrl: 'http://localhost:3001',

            /**
             * What to do when the API cannot be reached.
             *
             *   auto   - try the API, fall back to the JSON snapshot in
             *            app/fixtures/data if it does not answer (default)
             *   always - never call the API; always use the snapshot
             *   never  - never use the snapshot; a dead API is an error
             *
             * With "auto" there is nothing to change when the backend goes
             * live: point apiBase at it and the site starts using it.
             */
            offlineFallback: process.env.NUXT_PUBLIC_OFFLINE_FALLBACK ?? 'auto',
        },
    },

    app: {
        pageTransition: { name: 'page', mode: 'out-in' },
        layoutTransition: { name: 'layout', mode: 'out-in' },

        head: {
            charset: 'utf-8',
            viewport: 'width=device-width, initial-scale=1',
            // Single-locale site: English, left-to-right.
            htmlAttrs: { lang: 'en', dir: 'ltr' },
            link: [
                { rel: 'shortcut icon', type: 'image/png', href: '/assets/images/logo-header.png' },
                // Inter is served from Google Fonts. The preconnect pair opens
                // both connections early: the stylesheet comes from one host
                // and the font files from another, so without the second hint
                // the fonts wait on a fresh handshake.
                { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
                { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: 'anonymous' },
                {
                    rel: 'stylesheet',
                    href: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
                },
            ]
        },
    },

    devServer: { port: 3001 },

    css: ['~/assets/css/main.css'],

    vite: {
        plugins: [tailwindcss()],
    },

    modules: [
        '~~/modules/admin/index.ts',
        '~~/modules/client/index.ts',
        '@pinia/nuxt',
    ],

    /**
     * Components are registered without a directory prefix so a file at
     * components/ui/UiButton.vue is <UiButton />, not <UiUiButton />.
     */
    components: [
        { path: '~/components/ui', pathPrefix: false },
        { path: '~/components/common', pathPrefix: false },
        { path: '~/components', pathPrefix: false },
    ],

    imports: { autoImport: true },

    nitro: {
        compressPublicAssets: true,
        routeRules: {
            // Cache the immutable build output aggressively; everything else
            // is rendered per request so admin edits show up immediately.
            '/_nuxt/**': { headers: { 'cache-control': 'public, max-age=31536000, immutable' } },
        },
    },

    typescript: {
        strict: true,
        typeCheck: false,
    },
})
