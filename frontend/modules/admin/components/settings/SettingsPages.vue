<template>
    <div class="flex flex-col gap-5">
        <SettingsPanel
            title="About page"
            description="The full contents of the /about page."
            reset-key="about_page"
            @reset="$emit('reset', $event)">
            <UiInput v-model="aboutPage.title" label="Label" />

            <UiInput v-model="aboutPage.subtitle" label="Subtitle" />

            <UiImageUploader v-model="aboutPage.cover" label="Cover image" hint="Recommended size 1920×600 px." />

            <UiTextarea
                v-model="aboutPage.body"
                label="Body"
                :rows="10"
                :maxlength="20000"
                hint="Leave a blank line to start a new paragraph." />

            <UiRepeater
                v-model="aboutPage.stats"
                label="Key figures"
                add-label="Add figure"
                :factory="() => ({ value: '', label: '' })"
                :max="8">
                <template #default="{ item, update }">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <UiInput :model-value="item.value" label="Figure" @update:model-value="update({ value: $event })" />
                        <UiInput :model-value="item.label" label="Label" @update:model-value="update({ label: $event })" />
                    </div>
                </template>
            </UiRepeater>

            <UiRepeater
                v-model="aboutPage.sections"
                label="Additional sections"
                add-label="Add section"
                :factory="() => ({ title: '', text: '', image: '' })"
                :max="12">
                <template #default="{ item, update }">
                    <div class="grid gap-4 md:grid-cols-4">
                        <UiImageUploader :model-value="item.image" label="Image" @update:model-value="update({ image: $event })" />

                        <div class="grid gap-3 md:col-span-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <UiInput :model-value="item.title" label="Label" @update:model-value="update({ title: $event })" />
                            </div>

                            <UiTextarea :model-value="item.text" label="Text" :rows="4" @update:model-value="update({ text: $event })" />
                        </div>
                    </div>
                </template>
            </UiRepeater>

            <SeoFields
                v-model:meta-title="aboutPage.meta_title"
                v-model:meta-description="aboutPage.meta_description" />
        </SettingsPanel>

        <SettingsPanel
            title="Contact page"
            description="The full contents of the /contact page."
            reset-key="contact_page"
            @reset="$emit('reset', $event)">
            <UiInput v-model="contactPage.title" label="Label" />

            <UiInput v-model="contactPage.subtitle" label="Subtitle" />

            <UiImageUploader v-model="contactPage.cover" label="Cover image" />

            <UiTextarea v-model="contactPage.intro" label="Intro text" :rows="3" :maxlength="1000" />

            <UiTextarea v-model="contactPage.address" label="Address" :rows="2" />

            <div class="grid gap-4 sm:grid-cols-2">
                <UiInput v-model="contactPage.working_hours" label="Opening hours" />
                <UiSwitch v-model="contactPage.form_enabled" label="Show the contact form" class="pt-6" />
            </div>

            <UiRepeater
                v-model="phoneRows"
                label="Phone numbers"
                add-label="Add number"
                :factory="() => ({ value: '' })"
                :max="6">
                <template #default="{ item, update }">
                    <UiInput :model-value="item.value" label="Number" @update:model-value="update({ value: $event })" />
                </template>
            </UiRepeater>

            <UiRepeater
                v-model="emailRows"
                label="Email addresses"
                add-label="Add email"
                :factory="() => ({ value: '' })"
                :max="6">
                <template #default="{ item, update }">
                    <UiInput :model-value="item.value" label="Email" type="email" @update:model-value="update({ value: $event })" />
                </template>
            </UiRepeater>

            <UiTextarea
                v-model="contactPage.map_embed"
                label="Map embed"
                :rows="3"
                hint="A map URL or a full iframe snippet. Only its src is used; the markup is never injected into the page." />

            <SeoFields
                v-model:meta-title="contactPage.meta_title"
                v-model:meta-description="contactPage.meta_description" />
        </SettingsPanel>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { AboutPageSettings, ContactPageSettings, SettingsKey } from '~/types/settings'

const props = defineProps<{
    aboutPage: AboutPageSettings
    contactPage: ContactPageSettings
}>()

defineEmits<{ reset: [SettingsKey] }>()

/**
 * Phones and emails are stored as plain string arrays, but UiRepeater works
 * with objects. These adapters map between the two shapes so the storage
 * format stays simple and the editor stays generic.
 */
const phoneRows = computed({
    get: () => (props.contactPage.phones ?? []).map(value => ({ value })),
    set: (rows: { value: string }[]) => {
        props.contactPage.phones = rows.map(row => row.value)
    },
})

const emailRows = computed({
    get: () => (props.contactPage.emails ?? []).map(value => ({ value })),
    set: (rows: { value: string }[]) => {
        props.contactPage.emails = rows.map(row => row.value)
    },
})
</script>
