<template>
    <div :class="classes" :style="style" aria-hidden="true" />
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
    width?: string
    height?: string
    rounded?: 'sm' | 'full' | 'base'
}>(), {
    height: '1rem',
    rounded: 'base',
})

const rounds = { sm: 'rounded-sm', base: 'rounded-[var(--radius-base)]', full: 'rounded-full' }

const classes = computed(() => ['animate-pulse bg-surface-muted', rounds[props.rounded]])

const style = computed(() => ({ width: props.width ?? '100%', height: props.height }))
</script>
