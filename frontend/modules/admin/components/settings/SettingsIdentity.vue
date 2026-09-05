<template>
    <div class="flex flex-col gap-5">
        <SettingsPanel
            title="Site identity"
            eyebrow="Identity"
            description="The store’s name, tagline and logos."
            reset-key="identity"
            @reset="$emit('reset', $event)">
            <div class="grid gap-4 sm:grid-cols-2">
                <UiInput v-model="identity.site_name" label="Site name" />
                <UiInput v-model="identity.tagline" label="Tagline" />
            </div>

            <UiTextarea
                v-model="identity.description"
                label="Short description"
                :rows="3"
                :maxlength="500" />

            <div class="grid gap-4 sm:grid-cols-3">
                <UiImageUploader v-model="identity.logo" label="Logo (light theme)" fit="contain" />
                <UiImageUploader v-model="identity.logo_dark" label="Logo (dark theme)" fit="contain" />
                <UiImageUploader v-model="identity.logo_footer" label="Footer logo" fit="contain" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <UiImageUploader v-model="identity.favicon" label="Favicon" fit="contain" />
                <UiInput v-model.number="identity.logo_width" label="Logo width (px)" type="number" />
                <UiInput v-model.number="identity.logo_height" label="Logo height (px)" type="number" />
            </div>
        </SettingsPanel>

        <SettingsPanel
            title="Appearance"
            eyebrow="Theme"
            description="Colours, corner rounding and page width."
            reset-key="theme"
            @reset="$emit('reset', $event)">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="flex items-end gap-2">
                    <UiInput v-model="theme.primary" label="Primary colour" :rules="[hexColor()]" class="flex-1" />
                    <input v-model="theme.primary" type="color" class="h-10 w-12 cursor-pointer rounded-[var(--radius-base)] border border-border bg-surface" aria-label="Pick the primary colour" />
                </div>

                <div class="flex items-end gap-2">
                    <UiInput v-model="theme.accent" label="Accent colour" :rules="[hexColor()]" class="flex-1" />
                    <input v-model="theme.accent" type="color" class="h-10 w-12 cursor-pointer rounded-[var(--radius-base)] border border-border bg-surface" aria-label="Pick the accent colour" />
                </div>

                <UiSelect
                    v-model="theme.radius"
                    label="Corner rounding"
                    :options="[
                        { value: 'none', label: 'Square (classic)' },
                        { value: 'sm', label: 'Small' },
                        { value: 'md', label: 'Medium' },
                        { value: 'lg', label: 'Large' },
                    ]" />

                <UiSelect
                    v-model="theme.container"
                    label="Page width"
                    :options="[
                        { value: 'narrow', label: 'Narrow' },
                        { value: 'wide', label: 'Standard' },
                        { value: 'full', label: 'Full width' },
                    ]" />

                <UiSelect
                    v-model="theme.default_mode"
                    label="Default theme"
                    :options="[
                        { value: 'light', label: 'Light' },
                        { value: 'dark', label: 'Dark' },
                        { value: 'system', label: 'Match the visitor’s system' },
                    ]" />
            </div>
        </SettingsPanel>
    </div>
</template>

<script setup lang="ts">
import { hexColor } from '~/helpers/rules'
import type { IdentitySettings, SettingsKey, ThemeSettings } from '~/types/settings'

/**
 * The objects passed in are the settings store's own draft branches, so
 * binding v-model to their fields writes straight into the draft and the
 * "unsaved changes" indicator stays accurate. Only the fields are mutated,
 * never the props themselves.
 */
defineProps<{
    identity: IdentitySettings
    theme: ThemeSettings
}>()

defineEmits<{ reset: [SettingsKey] }>()
</script>
