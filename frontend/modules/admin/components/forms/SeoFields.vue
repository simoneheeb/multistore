<template>
    <fieldset class="rounded-[var(--radius-base)] border border-border p-4">
        <legend class="px-1.5 text-xs font-medium text-muted">
            SEO <span class="uppercase tracking-wider">SEO</span>
        </legend>

        <div class="grid gap-4 sm:grid-cols-2">
            <UiInput
                :model-value="metaTitle"
                label="Meta title"
                :maxlength="255"
                hint="About 60 characters works best. Leave empty to fall back to the name."
                @update:model-value="$emit('update:metaTitle', $event)" />

            <UiTextarea
                :model-value="metaDescription"
                label="Meta description"
                :rows="3"
                :maxlength="500"
                hint="About 160 characters. Leave empty to fall back to the description."
                @update:model-value="$emit('update:metaDescription', $event)" />
        </div>
    </fieldset>
</template>

<script setup lang="ts">
/**
 * Shared SEO override block used by the brand, category and product forms.
 * Both fields are optional: the API falls back to the record's own name and
 * description when they are empty.
 */
defineProps<{
    metaTitle?: string
    metaDescription?: string
}>()

defineEmits<{
    'update:metaTitle': [string]
    'update:metaDescription': [string]
}>()
</script>
