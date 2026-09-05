import { createError } from 'nuxt/app'
import { ApiError } from '~/composables/useApi'

/**
 * Re-raises a failed catalogue fetch with the status it deserves.
 *
 * Only a genuinely missing record is a 404. Every other failure - the API
 * throttling the request (429), breaking (5xx) or being unreachable - has to
 * keep its own status: answering a throttled visitor with "this product does
 * not exist" is wrong on its face, and it invites search engines to drop a
 * page that is perfectly healthy.
 *
 * @param error    The `error` ref value from useAsyncData.
 * @param resource Human-readable name used in the 404 message, e.g. "Brand".
 */
export function throwCatalogError(error: unknown, resource: string): never {
    const status = resolveStatus(error)

    // Status 0 is ApiError's marker for "no response at all" (offline, DNS,
    // CORS). 503 is the honest translation: the upstream is unavailable.
    const statusCode = status === 0 ? 503 : status

    throw createError({
        statusCode,
        statusMessage: statusCode === 404
            ? `${resource} not found`
            : messageFor(error, resource),
        fatal: true,
    })
}

/**
 * Nuxt wraps whatever useAsyncData's handler threw in a NuxtError, so the
 * original ApiError may sit one or two levels down in `cause`.
 */
function resolveStatus(error: unknown): number {
    let current: any = error

    for (let depth = 0; current && depth < 3; depth += 1) {
        if (current instanceof ApiError) return current.status
        if (typeof current.statusCode === 'number') return current.statusCode
        current = current.cause
    }

    return 500
}

function messageFor(error: unknown, resource: string): string {
    const message = (error as any)?.message

    return typeof message === 'string' && message.trim()
        ? message
        : `${resource} could not be loaded.`
}
