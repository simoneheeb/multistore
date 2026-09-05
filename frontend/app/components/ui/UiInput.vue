<template>
    <div class="w-full">
        <label v-if="label" :for="id" class="mb-1.5 flex items-baseline gap-2 text-sm font-medium text-heading">
            <span>{{ label }}</span>
            <span v-if="required" class="text-danger">*</span>
        </label>

        <div class="relative">
            <UiIcon
                v-if="icon"
                :name="icon"
                :size="17"
                class="pointer-events-none absolute inset-y-0 my-auto start-3 text-muted" />

            <input
                :id="id"
                ref="inputRef"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :required="required"
                :maxlength="maxlength"
                :aria-invalid="!!errorMessage"
                :aria-describedby="errorMessage ? `${id}-error` : hint ? `${id}-hint` : undefined"
                :class="inputClass"
                @input="onInput"
                @blur="touched = true" />

            <span v-if="suffix" class="absolute inset-y-0 my-auto end-3 flex items-center text-xs text-muted">
                {{ suffix }}
            </span>
        </div>

        <p v-if="errorMessage" :id="`${id}-error`" class="mt-1 text-xs text-danger">{{ errorMessage }}</p>
        <p v-else-if="hint" :id="`${id}-hint`" class="mt-1 text-xs text-muted">{{ hint }}</p>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, useId } from 'vue'
import { firstError, type ValidationRule } from '~/helpers/rules'

const props = withDefaults(defineProps<{
    modelValue?: string | number | null
    label?: string
    type?: string
    placeholder?: string
    hint?: string
    icon?: string
    suffix?: string
    disabled?: boolean
    readonly?: boolean
    required?: boolean
    maxlength?: number
    rules?: ValidationRule[]
    /** Externally supplied error, e.g. from a server validation response. */
    error?: string | null
}>(), {
    type: 'text',
    rules: () => [],
})

const emit = defineEmits<{ 'update:modelValue': [string] }>()

const id = useId()
const inputRef = ref<HTMLInputElement | null>(null)
const touched = ref(false)

// Client-side rule errors only appear after the field has been visited, so a
// pristine form is not covered in red before the user types anything.
const errorMessage = computed(() => {
    if (props.error) return props.error
    if (!touched.value) return null
    return firstError(props.modelValue, props.rules)
})

const inputClass = computed(() => [
    'w-full rounded-[var(--radius-base)] border bg-surface text-sm text-heading',
    'h-10 transition-colors placeholder:text-muted',
    'focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary',
    'disabled:cursor-not-allowed disabled:opacity-60',
    props.icon ? 'ps-10' : 'ps-3',
    props.suffix ? 'pe-12' : 'pe-3',
    errorMessage.value ? 'border-danger' : 'border-border',
])

const onInput = (event: Event) => {
    emit('update:modelValue', (event.target as HTMLInputElement).value)
}

defineExpose({ focus: () => inputRef.value?.focus() })
</script>
