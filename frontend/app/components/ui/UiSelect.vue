<template>
    <div class="w-full">
        <label v-if="label" :for="id" class="mb-1.5 flex items-baseline gap-2 text-sm font-medium text-heading">
            <span>{{ label }}</span>
            <span v-if="required" class="text-danger">*</span>
        </label>

        <div class="relative">
            <select
                :id="id"
                :value="modelValue ?? ''"
                :disabled="disabled"
                :required="required"
                :class="fieldClass"
                @change="onChange">
                <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>

                <option v-for="option in options" :key="String(option.value)" :value="option.value">
                    {{ option.label }}
                </option>
            </select>

            <UiIcon
                name="chevron-down"
                :size="16"
                class="pointer-events-none absolute inset-y-0 my-auto end-3 text-muted" />
        </div>

        <p v-if="error" class="mt-1 text-xs text-danger">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-xs text-muted">{{ hint }}</p>
    </div>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue'

export interface SelectOption {
    value: string | number
    label: string
}

const props = defineProps<{
    modelValue?: string | number | null
    options: SelectOption[]
    label?: string
    placeholder?: string
    hint?: string
    disabled?: boolean
    required?: boolean
    error?: string | null
}>()

const emit = defineEmits<{ 'update:modelValue': [string] }>()

const id = useId()

const fieldClass = computed(() => [
    'w-full appearance-none rounded-[var(--radius-base)] border bg-surface h-10 ps-3 pe-9 text-sm text-heading',
    'transition-colors focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary',
    'disabled:cursor-not-allowed disabled:opacity-60',
    props.error ? 'border-danger' : 'border-border',
])

const onChange = (event: Event) => {
    emit('update:modelValue', (event.target as HTMLSelectElement).value)
}
</script>
