import { useState } from 'nuxt/app'

export type ToastVariant = 'success' | 'error' | 'info' | 'warning'

export interface ToastItem {
    id: number
    variant: ToastVariant
    message: string
    title?: string
}

let counter = 0

/**
 * Minimal toast queue rendered by <UiToaster /> in the layouts.
 *
 * Kept in useState (not a plain module ref) so each SSR request gets its own
 * queue instead of sharing one across visitors.
 */
export function useToast() {
    const toasts = useState<ToastItem[]>('toasts', () => [])

    const push = (variant: ToastVariant, message: string, title?: string) => {
        // Toasts are a client-only affordance; calling this during SSR would
        // queue a message nobody ever sees.
        if (!import.meta.client || !message) return

        const id = ++counter
        toasts.value = [...toasts.value, { id, variant, message, title }]

        setTimeout(() => dismiss(id), 5000)
    }

    const dismiss = (id: number) => {
        toasts.value = toasts.value.filter(toast => toast.id !== id)
    }

    return {
        toasts,
        dismiss,
        success: (message: string, title?: string) => push('success', message, title),
        error: (message: string, title?: string) => push('error', message, title),
        info: (message: string, title?: string) => push('info', message, title),
        warning: (message: string, title?: string) => push('warning', message, title),
    }
}
