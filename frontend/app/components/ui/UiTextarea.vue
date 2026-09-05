<template>
    <div class="w-full">
        <label v-if="label" :for="id" class="mb-1.5 flex items-baseline gap-2 text-sm font-medium text-heading">
            <span>{{ label }}</span>
            <span v-if="required" class="text-danger">*</span>
        </label>

        <textarea
            :id="id"
            :value="modelValue"
            :rows="rows"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :maxlength="maxlength"
            :aria-invalid="!!errorMessage"
            :class="fieldClass"
            @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
            @blur="touched = true" />

        <div class="mt-1 flex items-start justify-between gap-3">
            <p v-if="errorMessage" class="text-xs text-danger">{{ errorMessage }}</p>
            <p v-else-if="hint" class="text-xs text-muted">{{ hint }}</p>

            <!-- Character counter, used by the SEO fields where the length
                 genuinely matters for how the snippet renders. -->
            <span v-if="maxlength" class="shrink-0 text-[0.6875rem] text-muted">
                {{ String(modelValue ?? '').length }} / {{ maxlength }}
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, useId } from 'vue'
import { firstError, type ValidationRule } from '~/helpers/rules'

const props = withDefaults(defineProps<{
    modelValue?: string | null
    label?: string
    placeholder?: string
    hint?: string
    rows?: number
    disabled?: boolean
    required?: boolean
    maxlength?: number
    rules?: ValidationRule[]
    error?: string | null
}>(), {
    rows: 4,
    rules: () => [],
})

defineEmits<{ 'update:modelValue': [string] }>()

const id = useId()
const touched = ref(false)

const errorMessage = computed(() => {
    if (props.error) return props.error
    if (!touched.value) return null
    return firstError(props.modelValue, props.rules)
})

const fieldClass = computed(() => [
    'w-full rounded-[var(--radius-base)] border bg-surface px-3 py-2.5 text-sm leading-7 text-heading',
    'transition-colors placeholder:text-muted resize-y',
    'focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary',
    'disabled:cursor-not-allowed disabled:opacity-60',
    errorMessage.value ? 'border-danger' : 'border-border',
])
</script>
