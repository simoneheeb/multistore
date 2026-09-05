<template>
    <div class="w-full">
        <label v-if="label" class="mb-1.5 flex items-baseline gap-2 text-sm font-medium text-heading">
            <span>{{ label }}</span>
            <span v-if="required" class="text-danger">*</span>
        </label>

        <div class="relative">
            <select
                :value="modelValue ?? ''"
                :disabled="disabled"
                :class="[
                    'w-full appearance-none rounded-[var(--radius-base)] border bg-surface h-10 ps-3 pe-9 text-sm text-heading',
                    'transition-colors focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary',
                    'disabled:cursor-not-allowed disabled:opacity-60',
                    error ? 'border-danger' : 'border-border',
                ]"
                @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value || null)">
                <option value="">{{ placeholder }}</option>

                <!--
                    The tree is flattened with a depth-based prefix, so the
                    hierarchy stays readable inside a native <select>, which
                    cannot nest beyond one <optgroup> level.
                -->
                <option v-for="option in options" :key="option.id" :value="option.id">
                    {{ indent(option.depth) }}{{ option.name }} — {{ option.name }}
                </option>
            </select>

            <UiIcon name="chevron-down" :size="16" class="pointer-events-none absolute inset-y-0 my-auto end-3 text-muted" />
        </div>

        <p v-if="error" class="mt-1 text-xs text-danger">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-xs text-muted">{{ hint }}</p>
    </div>
</template>

<script setup lang="ts">
import type { CategoryOption } from '~/types/interfaces'

withDefaults(defineProps<{
    modelValue?: string | null
    options: CategoryOption[]
    label?: string
    hint?: string
    placeholder?: string
    disabled?: boolean
    required?: boolean
    error?: string | null
}>(), {
    placeholder: 'No parent (top level)',
})

defineEmits<{ 'update:modelValue': [string | null] }>()

const indent = (depth: number) => (depth > 0 ? `${'—'.repeat(depth)} ` : '')
</script>
