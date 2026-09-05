<template>
    <div>
        <!--
            Section order and visibility come from the "home_sections" settings
            group, so an admin can reorder or hide any block of the landing
            page from the panel. The template renders whatever that list says,
            in the order it says.
        -->
        <template v-for="section in sections" :key="section.key">
            <HomeHero v-if="section.key === 'hero'" />

            <HomeFeatures v-else-if="section.key === 'features'" />

            <HomeCategories v-else-if="section.key === 'categories'" :categories="categories" />

            <HomePromo v-else-if="section.key === 'promo'" />

            <HomeProducts
                v-else-if="section.key === 'featured_products'"
                :products="featured"
                :loading="pending"
                tone="surface"
                eyebrow="Featured"
                title="Featured products"
                description="A selection of our newest and most popular products." />

            <HomeBrands v-else-if="section.key === 'brands'" :brands="brands" />

            <HomeProducts
                v-else-if="section.key === 'latest_products'"
                :products="latest"
                :loading="pending"
                eyebrow="New Arrivals"
                title="New arrivals"
                description="The newest items to arrive in the store." />

            <HomeAbout v-else-if="section.key === 'about'" />

            <HomeTestimonials v-else-if="section.key === 'testimonials'" />

            <HomeArticles v-else-if="section.key === 'articles'" />

            <!-- <HomeNewsletter v-else-if="section.key === 'newsletter'" /> -->

            <HomeContact v-else-if="section.key === 'contact'" />
        </template>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useAsyncData } from 'nuxt/app'
import { CatalogService } from '~~/modules/client/services/CatalogService'
import { useSiteSettings } from '~~/app/composables/useSiteSettings'
import { usePageSeo } from '~~/app/composables/useSeoDefaults'

const { get } = useSiteSettings()

/**
 * Everything the landing page needs, fetched in one server-side pass.
 *
 * A single useAsyncData with Promise.all means one render pass instead of
 * four sequential waterfalls, and the whole payload is serialised into the
 * HTML so the client does not refetch on hydration.
 */
const { data, pending } = useAsyncData('home-data', async () => {
    const [categories, brands, latest, featured] = await Promise.all([
        CatalogService.categories(),
        CatalogService.brands(),
        CatalogService.latestProducts(8),
        CatalogService.featuredProducts(8),
    ])

    return { categories, brands, latest, featured }
})

// Only top-level categories belong on the landing page; the rest are one
// click away on the category page itself.
const categories = computed(() =>
    (data.value?.categories ?? []).filter(category => !category.parent).slice(0, 12),
)

const brands = computed(() => data.value?.brands ?? [])
const latest = computed(() => data.value?.latest ?? [])
const featured = computed(() => data.value?.featured ?? [])

const sections = computed(() =>
    get<Array<{ key: string; enabled: boolean }>>('home_sections.sections', [])
        .filter(section => section.enabled),
)

// Getters, not values: the settings payload has not resolved yet when setup
// runs, so a plain read here would bake an empty title into the page.
usePageSeo({
    title: () => get('seo.default_title', ''),
    description: () => get('seo.default_description', ''),
    path: '/',
    type: 'website',
})
</script>
