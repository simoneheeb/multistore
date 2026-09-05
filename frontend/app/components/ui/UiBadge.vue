<template>
    <span :class="classes">
        <UiIcon v-if="icon" :name="icon" :size="13" />
        <slot />
    </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

type Tone = 'neutral' | 'primary' | 'accent' | 'success' | 'danger' | 'warning'

const props = withDefaults(defineProps<{ tone?: Tone; icon?: string }>(), { tone: 'neutral' })

const tones: Record<Tone, string> = {
    neutral: 'bg-surface-muted text-muted border-border',
    primary: 'bg-primary-soft text-primary-strong border-transparent',
    accent: 'bg-accent-soft text-accent border-transparent',
    success: 'bg-success/10 text-success border-transparent',
    danger: 'bg-danger/10 text-danger border-transparent',
    warning: 'bg-warning/10 text-warning border-transparent',
}

const classes = computed(() => [
    'inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[0.6875rem] font-medium leading-5',
    tones[props.tone],
])
</script>
