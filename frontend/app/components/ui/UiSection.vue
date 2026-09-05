<template>
    <section :class="sectionClass" :aria-labelledby="title ? id : undefined">
        <div class="site-container">
            <header v-if="title || $slots.actions" class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <UiTitle
                    v-if="title"
                    :id="id"
                    :eyebrow="eyebrow"
                    :title="title"
                    :description="description"
                    :align="align"
                    rule
                    :class="align === 'center' ? 'w-full' : ''" />

                <div v-if="$slots.actions" class="shrink-0">
                    <slot name="actions" />
                </div>
            </header>

            <slot />
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue'

/**
 * Standard vertical rhythm and container for every landing/page section, so
 * spacing stays consistent without each section repeating the same classes.
 */
const props = withDefaults(defineProps<{
    eyebrow?: string
    title?: string
    description?: string
    align?: 'start' | 'center'
    tone?: 'page' | 'surface' | 'muted'
    compact?: boolean
}>(), {
    align: 'start',
    tone: 'page',
})

const id = useId()

const tones = {
    page: '',
    surface: 'bg-surface',
    muted: 'bg-surface-muted',
}

const sectionClass = computed(() => [
    props.compact ? 'py-8 md:py-10' : 'py-12 md:py-16',
    tones[props.tone],
])
</script>
