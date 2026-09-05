<template>
    <div class="flex flex-col gap-5">
        <SettingsPanel
            title="Announcement bar"
            eyebrow="Top bar"
            description="The slim strip above the header."
            reset-key="header"
            @reset="$emit('reset', $event)">
            <UiSwitch v-model="header.topbar.enabled" label="Show the announcement bar" />

            <div class="grid gap-4 sm:grid-cols-3">
                <UiInput v-model="header.topbar.text" label="Text" />
                <UiInput v-model="header.topbar.phone" label="Phone number" />
            </div>
        </SettingsPanel>

        <SettingsPanel title="Header" description="Header behaviour and the main menu.">
            <div class="grid gap-4 sm:grid-cols-2">
                <UiSwitch v-model="header.sticky" label="Sticky header" description="Stays pinned to the top while scrolling." />
                <UiSwitch v-model="header.show_search" label="Show search" />
                <UiSwitch v-model="header.show_category_menu" label="Categories menu" description="A dropdown generated from the category tree." />
                <UiSwitch v-model="header.show_brand_menu" label="Brands menu" />
            </div>

            <UiRepeater
                v-model="header.menu_items"
                label="Main menu items"
                hint="A label and a URL for each link."
                add-label="Add link"
                :factory="() => ({ label: '', url: '/' })"
                :max="12">
                <template #default="{ item, update }">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <UiInput :model-value="item.label" label="Label" @update:model-value="update({ label: $event })" />
                        <UiInput :model-value="item.url" label="URL" :rules="[url()]" @update:model-value="update({ url: $event })" />
                    </div>
                </template>
            </UiRepeater>

            <fieldset class="rounded-[var(--radius-base)] border border-border p-4">
                <legend class="px-1.5 text-xs font-medium text-muted">Header call to action</legend>

                <UiSwitch v-model="header.cta.enabled" label="Show the button" class="mb-4" />

                <div class="grid gap-4 sm:grid-cols-3">
                    <UiInput v-model="header.cta.label" label="Label" />
                    <UiInput v-model="header.cta.url" label="URL" :rules="[url()]" />
                </div>
            </fieldset>
        </SettingsPanel>
    </div>
</template>

<script setup lang="ts">
import { url } from '~/helpers/rules'
import type { HeaderSettings, SettingsKey } from '~/types/settings'

defineProps<{ header: HeaderSettings }>()
defineEmits<{ reset: [SettingsKey] }>()
</script>
