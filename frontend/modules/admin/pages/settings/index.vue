<template>
    <DashboardPage
        title="Site settings"
        description="Every part of the site is editable from here.">
        <template #actions>
            <UiButton v-if="store.isDirty" variant="ghost" icon="refresh" @click="store.discard()">
                Discard changes
            </UiButton>

            <UiButton icon="save" :loading="store.saving" :disabled="!store.isDirty" @click="save">
                Save this tab
            </UiButton>
        </template>

        <div v-if="store.loading" class="flex flex-col gap-4">
            <UiSkeleton v-for="index in 4" :key="index" height="7rem" />
        </div>

        <template v-else-if="draft.identity">
            <UiAlert v-if="store.isDirty" tone="warning" class="mb-4">
                You have unsaved changes. Press “Save this tab” to apply them to the site.
            </UiAlert>

            <UiTabs v-model="tab" :tabs="tabs" class="mb-5" />

            <SettingsIdentity
                v-if="tab === 'identity'"
                :identity="draft.identity!"
                :theme="draft.theme!"
                @reset="reset" />

            <SettingsHeader
                v-else-if="tab === 'header'"
                :header="draft.header!"
                @reset="reset" />

            <SettingsFooter
                v-else-if="tab === 'footer'"
                :footer="draft.footer!"
                :social="draft.social!"
                @reset="reset" />

            <SettingsHome
                v-else-if="tab === 'home'"
                :home-sections="draft.home_sections!"
                :hero="draft.hero!"
                :features="draft.features!"
                :promo="draft.promo!"
                :testimonials="draft.testimonials!"
                :articles="draft.articles!"
                :newsletter="draft.newsletter!"
                @reset="reset" />

            <SettingsPages
                v-else-if="tab === 'pages'"
                :about-page="draft.about_page!"
                :contact-page="draft.contact_page!"
                @reset="reset" />

            <SettingsSeo
                v-else-if="tab === 'seo'"
                :seo="draft.seo!"
                @reset="reset" />
        </template>

        <UiEmpty v-else title="Settings could not be loaded" description="Check the connection to the server.">
            <UiButton variant="outline" icon="refresh" @click="store.fetch(true)">Try again</UiButton>
        </UiEmpty>
    </DashboardPage>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { definePageMeta } from '#imports'
import { useAdminSettingsStore } from '~~/modules/admin/stores/settings'
import type { SettingsKey } from '~/types/settings'

definePageMeta({ layout: 'dashboard' })

const store = useAdminSettingsStore()

const tab = ref<'identity' | 'header' | 'footer' | 'home' | 'pages' | 'seo'>('identity')

const tabs = [
    { value: 'identity', label: 'Identity', icon: 'image' },
    { value: 'header', label: 'Header', icon: 'menu' },
    { value: 'footer', label: 'Footer', icon: 'layers' },
    { value: 'home', label: 'Home page', icon: 'home' },
    { value: 'pages', label: 'Pages', icon: 'file' },
    { value: 'seo', label: 'SEO', icon: 'search' },
]

const draft = computed(() => store.draft)

/**
 * Which settings groups each tab owns. Saving writes only the groups of the
 * visible tab, so an unrelated draft change elsewhere is never pushed by
 * accident.
 */
const groupsByTab: Record<string, SettingsKey[]> = {
    identity: ['identity', 'theme'],
    header: ['header'],
    footer: ['footer', 'social'],
    home: ['home_sections', 'hero', 'features', 'promo', 'testimonials', 'articles', 'newsletter'],
    pages: ['about_page', 'contact_page'],
    seo: ['seo'],
}

onMounted(() => store.fetch())

const save = () => store.saveGroups(groupsByTab[tab.value] ?? [])

const reset = (key: SettingsKey) => store.resetGroup(key)

usePageSeo({ title: 'Site settings', noindex: true })
</script>
