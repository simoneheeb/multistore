<template>
    <div class="flex flex-col gap-5">
        <SettingsPanel
            title="Home page layout"
            eyebrow="Home layout"
            description="The order and visibility of the home page sections."
            reset-key="home_sections"
            @reset="$emit('reset', $event)">
            <UiAlert tone="info">
                Reordering these rows reorders the home page.
            </UiAlert>

            <UiRepeater
                v-model="homeSections.sections"
                label="Sections"
                add-label="Add section"
                :factory="() => ({ key: 'hero', enabled: true })"
                :max="20">
                <template #default="{ item, update }">
                    <div class="flex flex-wrap items-end gap-3">
                        <UiSelect
                            :model-value="item.key"
                            label="Section"
                            :options="sectionOptions"
                            class="flex-1"
                            @update:model-value="update({ key: $event })" />

                        <UiSwitch
                            :model-value="item.enabled"
                            label="Active"
                            class="pb-2"
                            @update:model-value="update({ enabled: $event })" />
                    </div>
                </template>
            </UiRepeater>
        </SettingsPanel>

        <SettingsPanel
            title="Home page slider"
            eyebrow="Hero"
            reset-key="hero"
            @reset="$emit('reset', $event)">
            <div class="grid gap-4 sm:grid-cols-2">
                <UiSwitch v-model="hero.autoplay" label="Autoplay" />
                <UiInput v-model.number="hero.interval" label="Interval between slides (ms)" type="number" />
            </div>

            <UiRepeater
                v-model="hero.slides"
                label="Slides"
                add-label="Add slide"
                :factory="() => ({ title: '', subtitle: '', description: '', image: '', cta_label: '', cta_url: '/products' })"
                :max="12">
                <template #default="{ item, update }">
                    <div class="grid gap-4 md:grid-cols-3">
                        <UiImageUploader
                            :model-value="item.image"
                            label="Image"
                            hint="Recommended size 1920×900 px."
                            @update:model-value="update({ image: $event })" />

                        <div class="grid gap-3 md:col-span-2">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <UiInput :model-value="item.title" label="Label" @update:model-value="update({ title: $event })" />
                            </div>

                            <UiInput :model-value="item.subtitle" label="Subtitle" @update:model-value="update({ subtitle: $event })" />
                            <UiTextarea :model-value="item.description" label="Description" :rows="2" @update:model-value="update({ description: $event })" />

                            <div class="grid gap-3 sm:grid-cols-2">
                                <UiInput :model-value="item.cta_label" label="Button label" @update:model-value="update({ cta_label: $event })" />
                                <UiInput :model-value="item.cta_url" label="Button URL" :rules="[url()]" @update:model-value="update({ cta_url: $event })" />
                            </div>
                        </div>
                    </div>
                </template>
            </UiRepeater>
        </SettingsPanel>

        <SettingsPanel
            title="Features and services"
            eyebrow="Features"
            reset-key="features"
            @reset="$emit('reset', $event)">
            <UiSwitch v-model="features.enabled" label="Show this section" />

            <UiInput v-model="features.title" label="Section heading" />

            <UiRepeater
                v-model="features.items"
                label="Items"
                add-label="Add item"
                :factory="() => ({ icon: 'star', title: '', description: '' })"
                :max="12">
                <template #default="{ item, update }">
                    <div class="grid gap-3 sm:grid-cols-4">
                        <UiSelect
                            :model-value="item.icon"
                            label="Icon"
                            :options="iconOptions"
                            @update:model-value="update({ icon: $event })" />

                        <UiInput :model-value="item.title" label="Label" @update:model-value="update({ title: $event })" />
                        <UiInput :model-value="item.description" label="Description" @update:model-value="update({ description: $event })" />
                    </div>
                </template>
            </UiRepeater>
        </SettingsPanel>

        <SettingsPanel
            title="Promo banner"
            eyebrow="Promo"
            reset-key="promo"
            @reset="$emit('reset', $event)">
            <UiSwitch v-model="promo.enabled" label="Show this section" />

            <div class="grid gap-4 md:grid-cols-3">
                <UiImageUploader v-model="promo.image" label="Banner image" />

                <div class="grid gap-3 md:col-span-2">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <UiInput v-model="promo.title" label="Label" />
                        <UiInput v-model="promo.badge" label="Badge" />
                    </div>

                    <UiTextarea v-model="promo.description" label="Description" :rows="3" />

                    <div class="grid gap-3 sm:grid-cols-2">
                        <UiInput v-model="promo.cta_label" label="Button label" />
                        <UiInput v-model="promo.cta_url" label="Button URL" :rules="[url()]" />
                    </div>
                </div>
            </div>
        </SettingsPanel>

        <SettingsPanel
            title="Customer reviews"
            eyebrow="Testimonials"
            reset-key="testimonials"
            @reset="$emit('reset', $event)">
            <UiSwitch v-model="testimonials.enabled" label="Show this section" />

            <UiInput v-model="testimonials.title" label="Section heading" />

            <UiRepeater
                v-model="testimonials.items"
                label="Reviews"
                add-label="Add review"
                :factory="() => ({ name: '', role: '', avatar: '', rating: 5, text: '' })"
                :max="24">
                <template #default="{ item, update }">
                    <div class="grid gap-4 md:grid-cols-4">
                        <UiImageUploader :model-value="item.avatar" label="Image" @update:model-value="update({ avatar: $event })" />

                        <div class="grid gap-3 md:col-span-3">
                            <div class="grid gap-3 sm:grid-cols-3">
                                <UiInput :model-value="item.name" label="Name" @update:model-value="update({ name: $event })" />
                                <UiInput :model-value="item.role" label="Role" @update:model-value="update({ role: $event })" />
                                <UiSelect
                                    :model-value="item.rating"
                                    label="Rating"
                                    :options="ratingOptions"
                                    @update:model-value="update({ rating: Number($event) })" />
                            </div>

                            <UiTextarea :model-value="item.text" label="Review text" :rows="3" :maxlength="600" @update:model-value="update({ text: $event })" />
                        </div>
                    </div>
                </template>
            </UiRepeater>
        </SettingsPanel>

        <SettingsPanel
            title="Articles"
            reset-key="articles"
            @reset="$emit('reset', $event)">
            <UiSwitch v-model="articles.enabled" label="Show this section" />

            <UiInput v-model="articles.title" label="Section heading" />

            <UiRepeater
                v-model="articles.items"
                label="Articles"
                add-label="Add article"
                :factory="() => ({ title: '', excerpt: '', image: '', url: '', date: '' })"
                :max="24">
                <template #default="{ item, update }">
                    <div class="grid gap-4 md:grid-cols-4">
                        <UiImageUploader :model-value="item.image" label="Image" @update:model-value="update({ image: $event })" />

                        <div class="grid gap-3 md:col-span-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <UiInput :model-value="item.title" label="Label" @update:model-value="update({ title: $event })" />
                            </div>

                            <UiTextarea :model-value="item.excerpt" label="Excerpt" :rows="2" @update:model-value="update({ excerpt: $event })" />

                            <div class="grid gap-3 sm:grid-cols-2">
                                <UiInput :model-value="item.url" label="Article URL" :rules="[url()]" @update:model-value="update({ url: $event })" />
                                <UiInput :model-value="item.date" label="Date" @update:model-value="update({ date: $event })" />
                            </div>
                        </div>
                    </div>
                </template>
            </UiRepeater>
        </SettingsPanel>

        <SettingsPanel
            title="Newsletter"
            reset-key="newsletter"
            @reset="$emit('reset', $event)">
            <UiSwitch v-model="newsletter.enabled" label="Show this section" />

            <div class="grid gap-4 sm:grid-cols-2">
                <UiInput v-model="newsletter.title" label="Label" />
                <UiInput v-model="newsletter.placeholder" label="Field placeholder" />
                <UiInput v-model="newsletter.button_label" label="Button label" />
            </div>

            <UiTextarea v-model="newsletter.description" label="Description" :rows="2" />
        </SettingsPanel>
    </div>
</template>

<script setup lang="ts">
import { url } from '~/helpers/rules'
import type {
    ArticleSettings,
    FeatureSettings,
    HeroSettings,
    HomeSectionsSettings,
    NewsletterSettings,
    PromoSettings,
    SettingsKey,
    TestimonialSettings,
} from '~/types/settings'

defineProps<{
    homeSections: HomeSectionsSettings
    hero: HeroSettings
    features: FeatureSettings
    promo: PromoSettings
    testimonials: TestimonialSettings
    articles: ArticleSettings
    newsletter: NewsletterSettings
}>()

defineEmits<{ reset: [SettingsKey] }>()

// Keys the landing page template knows how to render. Anything else would be
// stored but silently skipped, so the list is closed.
const sectionOptions = [
    { value: 'hero', label: 'Hero slider' },
    { value: 'features', label: 'Features and services' },
    { value: 'categories', label: 'Categories' },
    { value: 'promo', label: 'Promo banner' },
    { value: 'featured_products', label: 'Featured products' },
    { value: 'brands', label: 'Brands' },
    { value: 'latest_products', label: 'New arrivals' },
    { value: 'about', label: 'About us' },
    { value: 'testimonials', label: 'Customer reviews' },
    { value: 'articles', label: 'Articles' },
    { value: 'newsletter', label: 'Newsletter' },
    { value: 'contact', label: 'Quick contact' },
]

// Restricted to icons UiIcon can actually draw.
const iconOptions = [
    { value: 'truck', label: 'Send' },
    { value: 'shield', label: 'Guarantee' },
    { value: 'refresh', label: 'Returns' },
    { value: 'headset', label: 'Support' },
    { value: 'award', label: 'Quality' },
    { value: 'percent', label: 'Discount' },
    { value: 'clock', label: 'Time' },
    { value: 'heart', label: 'Satisfaction' },
    { value: 'star', label: 'Star' },
]

const ratingOptions = [1, 2, 3, 4, 5].map(value => ({ value, label: String(value) }))
</script>
