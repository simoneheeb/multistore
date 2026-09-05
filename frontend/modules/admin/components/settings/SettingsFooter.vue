<template>
    <div class="flex flex-col gap-5">
        <SettingsPanel
            title="Footer"
            description="Intro text, link columns and contact details."
            reset-key="footer"
            @reset="$emit('reset', $event)">
            <UiInput v-model="footer.about.title" label="Intro heading" />

            <UiTextarea v-model="footer.about.text" label="Intro text" :rows="3" :maxlength="1000" />

            <UiSwitch v-model="footer.show_newsletter" label="Show the newsletter form in the footer" />

            <UiRepeater
                v-model="footer.columns"
                label="Link columns"
                hint="Each column has a heading and a list of links."
                add-label="Add column"
                :factory="() => ({ title: '', links: [] })"
                :max="6">
                <template #default="{ item, update }">
                    <div class="flex flex-col gap-3">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <UiInput :model-value="item.title" label="Label" @update:model-value="update({ title: $event })" />
                        </div>

                        <UiRepeater
                            :model-value="item.links ?? []"
                            label="Links in this column"
                            add-label="Add link"
                            :factory="() => ({ label: '', url: '/' })"
                            :max="20"
                            @update:model-value="update({ links: $event })">
                            <template #default="{ item: link, update: updateLink }">
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <UiInput :model-value="link.label" label="Label" @update:model-value="updateLink({ label: $event })" />
                                    <UiInput :model-value="link.url" label="URL" :rules="[url()]" @update:model-value="updateLink({ url: $event })" />
                                </div>
                            </template>
                        </UiRepeater>
                    </div>
                </template>
            </UiRepeater>

            <fieldset class="rounded-[var(--radius-base)] border border-border p-4">
                <legend class="px-1.5 text-xs font-medium text-muted">Footer contact details</legend>

                <div class="grid gap-4 sm:grid-cols-2">
                    <UiInput v-model="footer.contact.title" label="Label" />
                    <UiInput v-model="footer.contact.phone" label="Landline" />
                    <UiInput v-model="footer.contact.mobile" label="Mobile" />
                    <UiInput v-model="footer.contact.email" label="Email" type="email" />
                    <UiInput v-model="footer.contact.working_hours" label="Opening hours" />
                </div>

                <UiTextarea v-model="footer.contact.address" label="Address" :rows="2" class="mt-4" />
            </fieldset>

            <UiInput v-model="footer.copyright" label="Copyright line" />
        </SettingsPanel>

        <SettingsPanel
            title="Social networks"
            eyebrow="Social"
            description="Shown in the footer and on the contact page."
            reset-key="social"
            @reset="$emit('reset', $event)">
            <UiRepeater
                v-model="social.items"
                label="Networks"
                hint="Pick a network so the correct icon is rendered."
                add-label="Add network"
                :factory="() => ({ platform: 'instagram', label: '', url: '' })"
                :max="12">
                <template #default="{ item, update }">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <UiSelect
                            :model-value="item.platform"
                            label="Network"
                            :options="platformOptions"
                            @update:model-value="update({ platform: $event })" />

                        <UiInput :model-value="item.label" label="Title" @update:model-value="update({ label: $event })" />
                        <UiInput :model-value="item.url" label="URL" :rules="[url()]" @update:model-value="update({ url: $event })" />
                    </div>
                </template>
            </UiRepeater>
        </SettingsPanel>
    </div>
</template>

<script setup lang="ts">
import { url } from '~/helpers/rules'
import type { FooterSettings, SettingsKey, SocialSettings } from '~/types/settings'

defineProps<{
    footer: FooterSettings
    social: SocialSettings
}>()

defineEmits<{ reset: [SettingsKey] }>()

// Restricted to the platforms UiIcon actually has a glyph for, so a typed
// value can never produce a blank icon.
const platformOptions = [
    { value: 'instagram', label: 'Instagram' },
    { value: 'telegram', label: 'Telegram' },
    { value: 'whatsapp', label: 'WhatsApp' },
    { value: 'twitter', label: 'X (Twitter)' },
    { value: 'linkedin', label: 'LinkedIn' },
    { value: 'link', label: 'Other' },
]
</script>
