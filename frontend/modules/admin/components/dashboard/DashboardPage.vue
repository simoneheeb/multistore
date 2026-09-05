<template>
    <div class="p-4 md:p-6">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <UiTitle :eyebrow="eyebrow" :title="title" :description="description" size="md" rule tag="h2" />

            <div class="flex flex-wrap items-center gap-2">
                <slot name="actions" />
            </div>
        </div>

        <slot />
    </div>
</template>

<script setup lang="ts">
/**
 * Shared page frame for every admin screen: a bilingual heading block, an
 * action slot, and consistent padding.
 */
defineProps<{
    title: string
    eyebrow?: string
    description?: string
}>()
</script>
