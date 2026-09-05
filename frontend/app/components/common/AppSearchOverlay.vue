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
                    class="fixed inset-0 z-[90] flex items-start justify-center bg-black/50 px-4 pt-24"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Search"
                    @click.self="close">
                    <div class="w-full max-w-xl rounded-[var(--radius-base)] border border-border bg-surface p-4 shadow-2xl">
                        <form class="flex items-center gap-2" @submit.prevent="submit">
                            <UiInput
                                ref="field"
                                v-model="term"
                                icon="search"
                                placeholder="Type a product name…"
                                class="flex-1" />

                            <UiButton type="submit" :disabled="!term.trim()">Search</UiButton>
                        </form>

                        <p class="mt-2 text-xs text-muted">
                            Press Esc to close.
                        </p>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </ClientOnly>
</template>

<script setup lang="ts">
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { navigateTo } from 'nuxt/app'

const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [boolean] }>()

const term = ref('')
const field = ref<{ focus: () => void } | null>(null)

const close = () => emit('update:modelValue', false)

const submit = () => {
    const query = term.value.trim()
    if (!query) return

    close()
    // Search is a filter on the products listing, not a separate page - one
    // result surface is easier to maintain and better for SEO.
    navigateTo({ path: '/products', query: { search: query } })
}

const onKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') close()
}

watch(() => props.modelValue, async (open) => {
    if (!import.meta.client) return

    if (open) {
        window.addEventListener('keydown', onKeydown)
        await nextTick()
        field.value?.focus()
    } else {
        window.removeEventListener('keydown', onKeydown)
        term.value = ''
    }
})

onBeforeUnmount(() => {
    if (import.meta.client) window.removeEventListener('keydown', onKeydown)
})
</script>
