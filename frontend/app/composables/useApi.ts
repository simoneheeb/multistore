import { useRuntimeConfig, useRequestEvent, useRequestHeaders } from 'nuxt/app'
import { getRequestIP } from 'h3'
import type { ApiEnvelope } from '~/types/interfaces'

/**
 * A failed API call, carrying the HTTP status so a caller can react to it.
 *
 * `useApi` normally swallows failures and returns undefined, which is right
 * for a widget that should just render empty. A page whose whole existence
 * depends on the response needs to tell "this record does not exist" (404)
 * apart from "the API refused or broke" (429, 500, network) - it must not
 * answer a throttled request with a Not Found page. Those callers opt in
 * with `throwOnError` and catch this.
 */
export class ApiError extends Error {
    constructor(public readonly status: number, message: string) {
        super(message)
        this.name = 'ApiError'
    }
}

/**
 * Statuses that mean "the API is not there", as opposed to an answer we do
 * not like. A 404 or a 422 came from a working backend and must be honoured;
 * a bad gateway did not.
 */
const UNREACHABLE_STATUSES = new Set([502, 503, 504])

/** How long a request waits before the API counts as unreachable. */
const REQUEST_TIMEOUT_MS = 8000

/**
 * How long to keep serving offline data before probing the API again. Without
 * this, every request on a page would wait out its own timeout; with it, a
 * backend that comes back is picked up within half a minute and no restart or
 * code change is needed.
 */
const RECHECK_AFTER_MS = 30_000

/** Timestamp of the failure that put us in offline mode; 0 while online. */
let offlineSince = 0
let offlineNoticeShown = false

/** True while the last failure is still recent enough to trust. */
const isOffline = () => offlineSince > 0 && Date.now() - offlineSince < RECHECK_AFTER_MS

/**
 * Whether the site is currently answering from the offline snapshot. Exposed
 * so a page can mention it; nothing in the app depends on it.
 */
export const useApiIsOffline = () => isOffline()

export interface ApiOptions {
    method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'
    body?: unknown
    query?: Record<string, unknown>
    /** Set false to suppress the automatic error toast (used by silent polls). */
    notifyOnError?: boolean
    /** Set true to show the API's success message as a toast. */
    notifyOnSuccess?: boolean
    /**
     * Set true to throw an ApiError instead of returning undefined, so the
     * caller can branch on the HTTP status. Status 0 means the request never
     * got a response at all.
     */
    throwOnError?: boolean
    /**
     * Set false to skip the offline fixture fallback for this one call.
     * Used by the health probe, which must be able to observe a real failure.
     */
    allowFixtures?: boolean
    headers?: Record<string, string>
}

/**
 * Single entry point for every API call.
 *
 * Runs on both the server (during SSR) and the client, so it never touches
 * `window` directly: the auth token comes from a cookie, which is readable in
 * both environments, and error toasts are only raised on the client.
 */
export async function useApi<T = unknown>(
    endpoint: string,
    options: ApiOptions = {},
): Promise<ApiEnvelope<T> | undefined> {
    const config = useRuntimeConfig()

    // During SSR an internal API host can be faster (or the only reachable
    // one); on the client the public URL is always used.
    const baseUrl = (import.meta.server && config.apiBaseServer)
        ? config.apiBaseServer
        : config.public.apiBase

    const {
        method = 'GET',
        body = null,
        query = {},
        notifyOnError = true,
        notifyOnSuccess = false,
        throwOnError = false,
        allowFixtures = true,
        headers: extraHeaders = {},
    } = options

    const url = buildUrl(String(baseUrl), endpoint, query)

    const isFormData = typeof FormData !== 'undefined' && body instanceof FormData

    const headers: Record<string, string> = {
        Accept: 'application/json',
        ...extraHeaders,
    }

    // FormData must set its own boundary, so the header is left off there.
    if (!isFormData && body) {
        headers['Content-Type'] = 'application/json'
    }

    const token = useAuthToken().value
    if (token) {
        headers.Authorization = `Bearer ${token}`
    }

    if (import.meta.server) {
        const forwarded = useRequestHeaders(['cookie', 'x-forwarded-for', 'user-agent'])

        // Forward the visitor's cookies so an authenticated server-side
        // render sees the same session as the browser would.
        if (forwarded.cookie) {
            headers.cookie = forwarded.cookie
        }

        // Forward the visitor's address. Every API call made while rendering
        // a page originates from this server, so without this header the API
        // would rate-limit all visitors as if they were one client. The API
        // only honours it from a proxy listed in its TRUSTED_PROXIES.
        const event = useRequestEvent()
        const clientIp = forwarded['x-forwarded-for']
            || (event ? getRequestIP(event, { xForwardedFor: true }) : undefined)
        if (clientIp) {
            headers['X-Forwarded-For'] = clientIp
        }

        if (forwarded['user-agent']) {
            headers['User-Agent'] = forwarded['user-agent']
        }
    }

    const fallbackMode = String(config.public.offlineFallback ?? 'auto')
    const mayFallBack = allowFixtures && fallbackMode !== 'never'

    // Forced offline, or still inside the window after a recent failure:
    // answer from the snapshot without waiting on a request that is very
    // likely to fail again.
    if (mayFallBack && (fallbackMode === 'always' || isOffline())) {
        const offline = await fromFixtures<T>(endpoint, query, method, notifyOnError, notifyOnSuccess)
        if (offline) return offline

        // The snapshot has no answer for this one - fall through and try the
        // API anyway, in case it is back.
    }

    try {
        const response = await fetch(url, {
            method,
            headers,
            body: isFormData ? (body as FormData) : body ? JSON.stringify(body) : undefined,
            // Without a deadline an unreachable-but-routable host hangs the
            // whole server-side render instead of failing over.
            signal: AbortSignal.timeout(REQUEST_TIMEOUT_MS),
        })

        const payload = await parseJson(response)

        if (!response.ok) {
            // A gateway error means the API itself is down, which is the same
            // situation as an unreachable host - fail over rather than
            // showing the visitor an empty page.
            if (mayFallBack && UNREACHABLE_STATUSES.has(response.status)) {
                goOffline(`the API answered ${response.status}`)

                const offline = await fromFixtures<T>(endpoint, query, method, notifyOnError, notifyOnSuccess)
                if (offline) return offline
            }

            handleError(response.status, payload, notifyOnError)

            if (throwOnError) {
                throw new ApiError(response.status, payload?.message || `Request failed with status ${response.status}.`)
            }

            return undefined
        }

        // A successful answer means the API is back; drop offline mode so the
        // next request goes straight out.
        offlineSince = 0

        if (notifyOnSuccess && payload?.message && import.meta.client) {
            useToast().success(payload.message)
        }

        return payload as ApiEnvelope<T>
    } catch (error) {
        // An ApiError is this function's own signal for an opted-in caller;
        // it has already been reported, so pass it straight through instead
        // of relabelling it as a network failure.
        if (error instanceof ApiError) throw error

        // Network-level failure (refused, DNS, CORS, timeout): the API is not
        // there. Serve the snapshot instead of an empty page, and remember
        // the failure so the rest of this render does not each wait its own
        // timeout.
        if (mayFallBack) {
            goOffline(describeFailure(error))

            const offline = await fromFixtures<T>(endpoint, query, method, notifyOnError, notifyOnSuccess)
            if (offline) return offline
        }

        // No offline answer either. There is no response to read, so surface
        // a message rather than a raw exception - and say plainly that a
        // write cannot be saved, since the snapshot is read-only and the
        // admin would otherwise be left guessing why nothing happened.
        if (notifyOnError && import.meta.client) {
            useToast().error(
                method === 'GET'
                    ? 'Could not reach the server. Check your internet connection.'
                    : 'The API is unreachable, so this change could not be saved. Offline data is read-only.',
            )
        }

        if (import.meta.server) {
            console.error(`[api] ${method} ${url} failed:`, error)
        }

        if (throwOnError) {
            throw new ApiError(0, 'Could not reach the server.')
        }

        return undefined
    }
}

/**
 * Answers a request from the offline snapshot.
 *
 * The fixtures module and its JSON are behind a dynamic import on purpose:
 * a deployment whose API works never loads any of it, so the payload costs
 * nothing until the moment it is needed.
 */
async function fromFixtures<T>(
    endpoint: string,
    query: Record<string, unknown>,
    method: string,
    notifyOnError: boolean,
    notifyOnSuccess: boolean,
): Promise<ApiEnvelope<T> | undefined> {
    try {
        const { resolveFixture } = await import('~/fixtures')
        const payload = await resolveFixture(endpoint, query, method)

        if (!payload) return undefined

        if (notifyOnSuccess && (payload as any)?.message && import.meta.client) {
            useToast().success((payload as any).message)
        }

        return payload as ApiEnvelope<T>
    } catch (error) {
        // A missing or malformed snapshot must not take the page down; the
        // caller falls back to its own empty state.
        console.error('[api] offline data could not be read:', error)

        if (notifyOnError && import.meta.client) {
            useToast().error('The server is unreachable and no offline data is available.')
        }

        return undefined
    }
}

/**
 * Enters offline mode and says so once, loudly enough to be noticed in a
 * terminal or a browser console but without repeating on every request.
 */
function goOffline(reason: string): void {
    offlineSince = Date.now()

    if (offlineNoticeShown) return
    offlineNoticeShown = true

    console.warn(
        `[api] ${reason}. Serving the offline snapshot from app/fixtures/data. `
        + 'Point NUXT_PUBLIC_API_BASE at a running API to use live data; '
        + 'run "pnpm fixtures" against it to refresh the snapshot.',
    )
}

/** A readable reason for the console notice. */
function describeFailure(error: unknown): string {
    const name = (error as any)?.name

    if (name === 'TimeoutError' || name === 'AbortError') {
        return `the API did not answer within ${REQUEST_TIMEOUT_MS / 1000}s`
    }

    return 'the API could not be reached'
}

/** Appends query parameters, skipping null/undefined/empty values. */
function buildUrl(baseUrl: string, endpoint: string, query: Record<string, unknown>): string {
    const url = `${baseUrl.replace(/\/$/, '')}/${endpoint.replace(/^\//, '')}`

    const params = new URLSearchParams()

    for (const [key, value] of Object.entries(query)) {
        if (value === null || value === undefined || value === '') continue
        params.append(key, String(value))
    }

    const search = params.toString()

    return search ? `${url}${url.includes('?') ? '&' : '?'}${search}` : url
}

/** A 204 or an empty body is valid; only try to parse when there is content. */
async function parseJson(response: Response): Promise<any> {
    const text = await response.text()

    if (!text) return null

    try {
        return JSON.parse(text)
    } catch {
        return null
    }
}

/**
 * Turns an API failure into user-visible feedback, and clears the session on
 * an authentication failure so the next guarded navigation redirects to login.
 */
function handleError(status: number, payload: any, notify: boolean): void {
    if (!import.meta.client) return

    if (status === 401) {
        useAuthToken().value = null
    }

    if (!notify) return

    const toast = useToast()

    // Validation errors: surface each field message, they are what the user
    // actually needs to act on.
    if (payload?.errors && typeof payload.errors === 'object') {
        for (const messages of Object.values(payload.errors as Record<string, string[]>)) {
            for (const message of messages) toast.error(message)
        }
        return
    }

    toast.error(payload?.message || 'Something went wrong. Please try again.')
}
