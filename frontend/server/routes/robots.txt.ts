import { defineEventHandler, setHeader } from 'h3'
import { useRuntimeConfig } from 'nitropack/runtime'

/**
 * Serves /robots.txt from the API.
 *
 * Crawlers look for robots.txt on the site's own origin, but the directives
 * (and the index/noindex switch) are managed in the admin panel, so the file
 * is proxied from the backend rather than shipped as a static asset.
 */
export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig(event)
    const apiBase = String(config.apiBaseServer || config.public.apiBase).replace(/\/$/, '')

    setHeader(event, 'Content-Type', 'text/plain; charset=utf-8')
    setHeader(event, 'Cache-Control', 'public, max-age=3600')

    try {
        return await $fetch<string>(`${apiBase}/robots.txt`, { responseType: 'text' })
    } catch {
        // A crawler must never get a 500 here: an unreachable API falls back
        // to a permissive default rather than blocking indexing outright.
        return `User-agent: *\nDisallow: /admin\nDisallow: /auth\nAllow: /\n`
    }
})
