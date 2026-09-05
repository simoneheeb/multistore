<template>
    <div class="flex flex-col items-center justify-center gap-3 rounded-[var(--radius-base)] border border-dashed border-border px-6 py-14 text-center">
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-surface-muted text-muted">
            <UiIcon :name="icon" :size="22" />
        </span>

        <p class="text-sm font-medium text-heading">{{ title }}</p>
        <p v-if="description" class="max-w-sm text-xs leading-6 text-muted">{{ description }}</p>

        <slot />
    </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
    title: string
    description?: string
    icon?: string
}>(), {
    icon: 'box',
})
</script>
