<template>
    <label class="flex cursor-pointer items-start gap-3 select-none" :class="disabled && 'cursor-not-allowed opacity-60'">
        <button
            type="button"
            role="switch"
            :aria-checked="modelValue"
            :disabled="disabled"
            :class="trackClass"
            @click="toggle">
            <span :class="knobClass" />
        </button>

        <span v-if="label || description" class="min-w-0">
            <span class="block text-sm font-medium text-heading">{{ label }}</span>
            <span v-if="description" class="mt-0.5 block text-xs leading-6 text-muted">{{ description }}</span>
        </span>
    </label>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    modelValue: boolean
    label?: string
    description?: string
    disabled?: boolean
}>()

const emit = defineEmits<{ 'update:modelValue': [boolean] }>()

const trackClass = computed(() => [
    'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full border transition-colors',
    'focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/30',
    props.modelValue ? 'bg-primary border-primary' : 'bg-surface-muted border-border',
])

// The knob travels toward the inline end, so it moves the correct way in RTL.
const knobClass = computed(() => [
    'absolute h-4 w-4 rounded-full bg-white shadow-sm transition-all',
    props.modelValue ? 'end-1' : 'start-1',
])

const toggle = () => {
    if (!props.disabled) emit('update:modelValue', !props.modelValue)
}
</script>
