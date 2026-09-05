<template>
    <ClientOnly>
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="opacity-0">
                <div
                    v-if="modelValue"
                    class="fixed inset-0 z-[90] flex items-end justify-center overflow-y-auto bg-black/45 p-0 sm:items-center sm:p-6"
                    role="dialog"
                    aria-modal="true"
                    @click.self="close">
                    <div :class="panelClass">
                        <header class="flex items-start justify-between gap-4 border-b border-border px-5 py-4">
                            <div class="min-w-0">
                                <h2 class="truncate text-base font-bold text-heading">{{ title }}</h2>
                                <p v-if="description" class="mt-0.5 text-xs text-muted">{{ description }}</p>
                            </div>

                            <button
                                type="button"
                                class="shrink-0 rounded p-1 text-muted transition-colors hover:bg-surface-muted hover:text-heading"
                                aria-label="Close"
                                @click="close">
                                <UiIcon name="close" :size="18" />
                            </button>
                        </header>

                        <div class="max-h-[70vh] overflow-y-auto px-5 py-4">
                            <slot />
                        </div>

                        <footer v-if="$slots.footer" class="flex items-center justify-end gap-2 border-t border-border px-5 py-3">
                            <slot name="footer" />
                        </footer>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </ClientOnly>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue'

const props = withDefaults(defineProps<{
    modelValue: boolean
    title: string
    description?: string
    size?: 'sm' | 'md' | 'lg' | 'xl'
    /** Blocks closing while a save is in flight. */
    persistent?: boolean
}>(), { size: 'md' })

const emit = defineEmits<{ 'update:modelValue': [boolean] }>()

const sizes = { sm: 'max-w-md', md: 'max-w-xl', lg: 'max-w-3xl', xl: 'max-w-5xl' }

const panelClass = computed(() => [
    'w-full rounded-t-xl border border-border bg-surface shadow-2xl sm:rounded-[var(--radius-base)]',
    sizes[props.size],
])

const close = () => {
    if (!props.persistent) emit('update:modelValue', false)
}

const onKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') close()
}

// Lock the page behind the dialog and wire up Escape only while it is open.
watch(() => props.modelValue, (open) => {
    if (!import.meta.client) return

    document.body.style.overflow = open ? 'hidden' : ''
    open
        ? window.addEventListener('keydown', onKeydown)
        : window.removeEventListener('keydown', onKeydown)
}, { immediate: true })

onBeforeUnmount(() => {
    if (!import.meta.client) return
    document.body.style.overflow = ''
    window.removeEventListener('keydown', onKeydown)
})
</script>
