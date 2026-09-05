<template>
    <div :class="classes" role="note">
        <UiIcon :name="icon" :size="17" class="mt-0.5 shrink-0" />

        <div class="min-w-0 text-xs leading-6">
            <p v-if="title" class="font-medium">{{ title }}</p>
            <slot />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

type Tone = 'info' | 'success' | 'warning' | 'danger'

const props = withDefaults(defineProps<{ tone?: Tone; title?: string }>(), { tone: 'info' })

const tones: Record<Tone, string> = {
    info: 'bg-info/8 text-info border-info/25',
    success: 'bg-success/8 text-success border-success/25',
    warning: 'bg-warning/8 text-warning border-warning/25',
    danger: 'bg-danger/8 text-danger border-danger/25',
}

const icons: Record<Tone, string> = {
    info: 'info',
    success: 'check-circle',
    warning: 'alert-circle',
    danger: 'alert-circle',
}

const icon = computed(() => icons[props.tone])

const classes = computed(() => [
    'flex items-start gap-2.5 rounded-[var(--radius-base)] border p-3',
    tones[props.tone],
])
</script>
