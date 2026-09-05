<template>
    <div class="w-full">
        <label v-if="label" class="mb-1.5 flex items-baseline gap-2 text-sm font-medium text-heading">
            <span>{{ label }}</span>
            <span v-if="required" class="text-danger">*</span>
        </label>

        <div class="rounded-[var(--radius-base)] border border-dashed border-border p-3">
            <div v-if="previews.length" class="mb-3 flex flex-wrap gap-2">
                <div v-for="(item, index) in previews" :key="index" class="relative">
                    <UiImage :src="item" alt="" ratio="square" class="h-20 w-20" />

                    <button
                        type="button"
                        class="absolute -top-1.5 -end-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-danger text-white"
                        aria-label="Remove file"
                        @click="removeAt(index)">
                        <UiIcon name="close" :size="12" />
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <UiButton variant="outline" size="sm" icon="upload" @click="picker?.click()">
                    {{ multiple ? 'Choose files' : 'Choose file' }}
                </UiButton>

                <span class="text-xs text-muted">{{ hint }}</span>
            </div>

            <input
                ref="picker"
                type="file"
                :accept="accept"
                :multiple="multiple"
                class="hidden"
                @change="onPick" />

            <p v-if="error" class="mt-1.5 text-xs text-danger">{{ error }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'

/**
 * Raw-File picker used by the product form.
 *
 * Unlike UiImageUploader this emits real File objects, because products are
 * submitted as multipart/form-data - the backend stores the files on disk
 * itself rather than accepting base64.
 */
const props = withDefaults(defineProps<{
    modelValue: File[]
    label?: string
    hint?: string
    accept?: string
    multiple?: boolean
    required?: boolean
    maxSize?: number
    maxFiles?: number
    /** Existing images to show before a new file is chosen. */
    existing?: string[]
}>(), {
    accept: 'image/png,image/jpeg,image/webp',
    maxSize: 5,
    maxFiles: 12,
    existing: () => [],
})

const emit = defineEmits<{ 'update:modelValue': [File[]] }>()

const picker = ref<HTMLInputElement | null>(null)
const error = ref<string | null>(null)
const previews = ref<string[]>([...props.existing])

// Object URLs created for previews have to be revoked, otherwise every pick
// leaks a blob for the lifetime of the page.
let objectUrls: string[] = []

const releaseUrls = () => {
    objectUrls.forEach(url => URL.revokeObjectURL(url))
    objectUrls = []
}

const rebuildPreviews = (files: File[]) => {
    releaseUrls()

    if (!files.length) {
        previews.value = [...props.existing]
        return
    }

    objectUrls = files.map(file => URL.createObjectURL(file))
    previews.value = objectUrls
}

watch(() => props.existing, (value) => {
    if (!props.modelValue.length) previews.value = [...value]
})

const onPick = (event: Event) => {
    error.value = null

    const picked = Array.from((event.target as HTMLInputElement).files ?? [])
    if (!picked.length) return

    if (picked.length > props.maxFiles) {
        error.value = `You may choose at most ${props.maxFiles} files.`
        return
    }

    const tooLarge = picked.find(file => file.size > props.maxSize * 1024 * 1024)
    if (tooLarge) {
        error.value = `Each file may not be larger than ${props.maxSize} MB.`
        return
    }

    rebuildPreviews(picked)
    emit('update:modelValue', picked)
}

const removeAt = (index: number) => {
    const next = [...props.modelValue]
    next.splice(index, 1)
    rebuildPreviews(next)
    emit('update:modelValue', next)
}

onBeforeUnmount(releaseUrls)
</script>
