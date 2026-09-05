<template>
    <div class="flex flex-col gap-5">
        <SettingsPanel
            title="Site SEO"
            eyebrow="SEO"
            description="These defaults apply to any page without its own values."
            reset-key="seo"
            @reset="$emit('reset', $event)">
            <UiAlert :tone="seo.robots_index ? 'success' : 'warning'">
                <template v-if="seo.robots_index">
                    The site is indexable and appears in sitemap.xml.
                </template>
                <template v-else>
                    Indexing is turned off; robots.txt tells search engines to stay away.
                </template>
            </UiAlert>

            <UiSwitch
                v-model="seo.robots_index"
                label="Allow search engines to index the site"
                description="Only turn this off while the site is being prepared." />

            <UiInput v-model="seo.default_title" label="Default title" />

            <UiInput
                v-model="seo.title_template"
                label="Title template"
                hint="%s is replaced by each page’s own title. Example: %s | Beta Official" />

            <UiTextarea
                v-model="seo.default_description"
                label="Default description"
                :rows="3"
                :maxlength="320"
                hint="About 160 characters displays in full in search results." />

            <UiInput
                v-model="seo.default_keywords"
                label="Keywords"
                hint="Separate with commas." />

            <div class="grid gap-4 sm:grid-cols-2">
                <UiImageUploader
                    v-model="seo.og_image"
                    label="Share image"
                    hint="Recommended size 1200×630 px. Used when a link is shared on social media." />

                <div class="flex flex-col gap-4">
                    <UiInput v-model="seo.twitter_handle" label="X (Twitter) handle" placeholder="@username" />
                    <UiInput
                        v-model="seo.google_site_verification"
                        label="Google verification code"
                        hint="The content value of the google-site-verification tag." />
                </div>
            </div>
        </SettingsPanel>

        <SettingsPanel
            title="Organisation details"
            eyebrow="Organization"
            description="Used in the schema.org structured data sent to search engines.">
            <div class="grid gap-4 sm:grid-cols-2">
                <UiInput v-model="seo.organization.name" label="Organisation name" />
                <UiInput v-model="seo.organization.legal_name" label="Legal name" />
                <UiInput v-model="seo.organization.phone" label="Phone" />
                <UiInput v-model="seo.organization.address" label="Address" />
            </div>

            <UiImageUploader v-model="seo.organization.logo" label="Organisation logo" fit="contain" />
        </SettingsPanel>
    </div>
</template>

<script setup lang="ts">
import type { SeoSettings, SettingsKey } from '~/types/settings'

defineProps<{ seo: SeoSettings }>()
defineEmits<{ reset: [SettingsKey] }>()
</script>
