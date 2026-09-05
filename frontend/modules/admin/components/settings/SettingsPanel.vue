<template>
    <section class="rounded-[var(--radius-base)] border border-border bg-surface">
        <header class="flex flex-wrap items-start justify-between gap-3 border-b border-border px-5 py-4">
            <UiTitle :eyebrow="eyebrow" :title="title" :description="description" size="sm" tag="h3" />

            <UiButton v-if="resetKey" variant="ghost" size="sm" icon="refresh" @click="$emit('reset', resetKey)">
                Restore defaults
            </UiButton>
        </header>

        <div class="flex flex-col gap-5 px-5 py-5">
            <slot />
        </div>
    </section>
</template>

<script setup lang="ts">
import type { SettingsKey } from '~/types/settings'

/**
 * One card per settings group inside a tab. The reset action is exposed here
 * rather than per field, because the backend resets a whole group at a time.
 */
defineProps<{
    title: string
    eyebrow?: string
    description?: string
    resetKey?: SettingsKey
}>()

defineEmits<{ reset: [SettingsKey] }>()
</script>
