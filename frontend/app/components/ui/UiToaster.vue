<template>
    <!-- ClientOnly: toasts are triggered by user actions, so rendering them
         during SSR would only produce markup that hydration discards. -->
    <ClientOnly>
        <div class="pointer-events-none fixed inset-x-0 bottom-0 z-[100] flex flex-col items-center gap-2 p-4 sm:items-end">
            <TransitionGroup
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="translate-y-2 opacity-0"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="translate-y-1 opacity-0">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    role="status"
                    :class="[
                        'pointer-events-auto flex w-full max-w-sm items-start gap-2.5 rounded-[var(--radius-base)]',
                        'border bg-surface p-3 shadow-lg',
                        borders[toast.variant],
                    ]">
                    <UiIcon :name="icons[toast.variant]" :size="18" :class="colors[toast.variant]" class="mt-0.5 shrink-0" />

                    <div class="min-w-0 flex-1">
                        <p v-if="toast.title" class="text-sm font-medium text-heading">{{ toast.title }}</p>
                        <p class="text-xs leading-6 text-body">{{ toast.message }}</p>
                    </div>

                    <button
                        type="button"
                        class="shrink-0 rounded p-0.5 text-muted transition-colors hover:text-heading"
                        aria-label="Close"
                        @click="dismiss(toast.id)">
                        <UiIcon name="close" :size="15" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </ClientOnly>
</template>

<script setup lang="ts">
import type { ToastVariant } from '~/composables/useToast'

const { toasts, dismiss } = useToast()

const icons: Record<ToastVariant, string> = {
    success: 'check-circle',
    error: 'alert-circle',
    warning: 'alert-circle',
    info: 'info',
}

const colors: Record<ToastVariant, string> = {
    success: 'text-success',
    error: 'text-danger',
    warning: 'text-warning',
    info: 'text-info',
}

const borders: Record<ToastVariant, string> = {
    success: 'border-success/30',
    error: 'border-danger/30',
    warning: 'border-warning/30',
    info: 'border-info/30',
}
</script>
