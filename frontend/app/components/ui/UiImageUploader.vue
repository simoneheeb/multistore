<template>
    <div class="w-full">
        <label v-if="label" class="mb-1.5 flex items-baseline gap-2 text-sm font-medium text-heading">
            <span>{{ label }}</span>
        </label>

        <div
            :class="[
                'relative flex items-center gap-4 rounded-[var(--radius-base)] border border-dashed p-3 transition-colors',
                dragging ? 'border-primary bg-primary-soft/40' : 'border-border',
            ]"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop">
            <UiImage
                :src="preview"
                :alt="label ?? ''"
                :fit="fit"
                ratio="square"
                class="h-20 w-20 shrink-0" />

            <div class="min-w-0 flex-1">
                <p class="text-xs leading-6 text-muted">
                    {{ hint ?? 'Drag an image here, or choose one below.' }}
                </p>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <UiButton variant="outline" size="sm" icon="upload" @click="picker?.click()">
                        Choose image
                    </UiButton>

                    <UiButton v-if="preview" variant="ghost" size="sm" icon="trash" @click="clear">
                        Remove
                    </UiButton>
                </div>

                <p v-if="error" class="mt-1.5 text-xs text-danger">{{ error }}</p>
            </div>

            <input
                ref="picker"
                type="file"
                :accept="accept"
                class="hidden"
                @change="onPick" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'

/**
 * Image field for the settings editor.
 *
 * Emits a base64 data URL rather than uploading separately: the settings API
 * accepts inline images and moves them to disk itself, which keeps a whole
 * settings tab a single atomic save instead of an upload plus a save that can
 * half-fail.
 */
const props = withDefaults(defineProps<{
    /** Either an existing URL or a data URL from a previous pick. */
    modelValue?: string | null
    label?: string
    hint?: string
    accept?: string
    /** Megabytes. Must stay at or below the backend's own 5 MB limit. */
    maxSize?: number
    fit?: 'cover' | 'contain'
}>(), {
    accept: 'image/png,image/jpeg,image/webp,image/gif,image/svg+xml',
    maxSize: 5,
    fit: 'cover',
})

const emit = defineEmits<{ 'update:modelValue': [string] }>()

const picker = ref<HTMLInputElement | null>(null)
const dragging = ref(false)
const error = ref<string | null>(null)

const preview = computed(() => props.modelValue || '')

const read = (file: File) => {
    error.value = null

    if (!file.type.startsWith('image/')) {
        error.value = 'Only image files are allowed.'
        return
    }

    if (file.size > props.maxSize * 1024 * 1024) {
        error.value = `The image may not be larger than ${props.maxSize} MB.`
        return
    }

    const reader = new FileReader()
    reader.onload = () => emit('update:modelValue', String(reader.result))
    reader.onerror = () => { error.value = 'The file could not be read.' }
    reader.readAsDataURL(file)
}

const onPick = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0]
    if (file) read(file)
}

const onDrop = (event: DragEvent) => {
    dragging.value = false
    const file = event.dataTransfer?.files?.[0]
    if (file) read(file)
}

const clear = () => {
    error.value = null
    emit('update:modelValue', '')
    if (picker.value) picker.value.value = ''
}
</script>
