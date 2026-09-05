<template>
    <div v-if="product" class="py-8 md:py-10">
        <div class="site-container">
            <UiBreadcrumbs :items="crumbs" class="mb-5" />

            <div class="grid gap-8 lg:grid-cols-2">
                <!-- Gallery -->
                <div>
                    <UiImage
                        :src="activeImage"
                        :alt="product.name"
                        ratio="square"
                        eager
                        class="w-full border border-border" />

                    <ul v-if="gallery.length > 1" class="mt-3 flex flex-wrap gap-2">
                        <li v-for="(image, index) in gallery" :key="index">
                            <button
                                type="button"
                                :aria-label="`Image ${index + 1}`"
                                :class="[
                                    'block overflow-hidden rounded-[var(--radius-base)] border-2 transition-colors',
                                    image === activeImage ? 'border-primary' : 'border-border hover:border-border-strong',
                                ]"
                                @click="activeImage = image">
                                <UiImage :src="image" alt="" ratio="square" :rounded="false" class="h-16 w-16" />
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Summary -->
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <UiBadge v-if="product.is_new" tone="primary">New</UiBadge>
                        <UiBadge v-if="product.brand" tone="neutral">{{ product.brand.name }}</UiBadge>
                    </div>

                    <p v-if="product.name" class="mt-3 text-xs uppercase tracking-[0.18em] text-primary">
                        {{ product.name }}
                    </p>

                    <h1 class="mt-1 text-2xl font-bold leading-snug text-heading md:text-3xl">
                        {{ product.name }}
                    </h1>

                    <dl class="mt-5 flex flex-wrap gap-x-6 gap-y-2 border-y border-border py-3 text-xs">
                        <div v-if="product.pid" class="flex items-center gap-1.5">
                            <dt class="text-muted">SKU:</dt>
                            <dd class="font-medium text-heading">{{ product.pid }}</dd>
                        </div>

                        <div v-if="product.category" class="flex items-center gap-1.5">
                            <dt class="text-muted">Category:</dt>
                            <dd>
                                <NuxtLink
                                    :to="`/categories/${product.category.slug}`"
                                    class="font-medium text-primary transition-colors hover:underline">
                                    {{ product.category.name }}
                                </NuxtLink>
                            </dd>
                        </div>

                        <div v-if="product.brand" class="flex items-center gap-1.5">
                            <dt class="text-muted">Brand:</dt>
                            <dd>
                                <NuxtLink
                                    :to="`/brands/${product.brand.slug}`"
                                    class="font-medium text-primary transition-colors hover:underline">
                                    {{ product.brand.name }}
                                </NuxtLink>
                            </dd>
                        </div>
                    </dl>

                    <p v-if="product.description" class="mt-5 text-sm leading-8 text-body">
                        {{ product.description }}
                    </p>

                    <!-- Attributes table -->
                    <div v-if="attributes.length" class="mt-6">
                        <h2 class="mb-2 text-sm font-bold text-heading">Specifications</h2>

                        <table class="w-full border-collapse overflow-hidden rounded-[var(--radius-base)] border border-border text-xs">
                            <tbody>
                                <tr
                                    v-for="(attribute, index) in attributes"
                                    :key="index"
                                    class="border-b border-border last:border-0 odd:bg-surface-muted/50">
                                    <th scope="row" class="w-40 px-3 py-2.5 text-start font-medium text-muted">
                                        {{ attribute.key }}
                                    </th>
                                    <td class="px-3 py-2.5 text-body">{{ attribute.value }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-7 flex flex-wrap gap-2">
                        <UiButton :href="`tel:${phone}`" icon="phone" size="lg">
                            Call to order
                        </UiButton>

                        <UiButton to="/contact" variant="outline" size="lg">
                            Send a message
                        </UiButton>
                    </div>
                </div>
            </div>

            <!-- English description, kept as a secondary block below the fold
                 so the Persian copy leads the page. -->
            <section v-if="product.description" class="mt-10 rounded-[var(--radius-base)] border border-border bg-surface p-5">
                <h2 class="text-[0.6875rem] uppercase tracking-[0.18em] text-primary">Description</h2>
                <p class="mt-2 text-sm leading-7 text-muted">{{ product.description }}</p>
            </section>
        </div>

        <UiSection
            v-if="related.length"
            tone="surface"
            eyebrow="Related"
            title="Related products"
            class="mt-10">
            <ProductGrid :products="related" />
        </UiSection>
    </div>

    <div v-else class="site-container py-20">
        <UiEmpty title="Product not found" description="This product may have been removed.">
            <UiButton to="/products" variant="outline">Back to products</UiButton>
        </UiEmpty>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { createError, useAsyncData, useRoute, useRuntimeConfig } from 'nuxt/app'
import { throwCatalogError } from '~/utils/catalogError'
import { CatalogService } from '~~/modules/client/services/CatalogService'
import { buildBreadcrumbJsonLd } from '~/composables/useSeoDefaults'

const route = useRoute()
const config = useRuntimeConfig()
const { get } = useSiteSettings()

const slug = computed(() => String(route.params.slug))

const { data, error } = await useAsyncData(
    () => `product-${slug.value}`,
    () => CatalogService.product(slug.value),
    { watch: [slug] },
)

// A missing product must answer with a real 404, not a 200 page saying "not
// found" - otherwise search engines index the empty shell.
// A failed request keeps its own status; only an empty response is a 404.
if (error.value) {
    throwCatalogError(error.value, 'Product')
}

if (!data.value?.product) {
    throw createError({ statusCode: 404, statusMessage: 'Product not found', fatal: true })
}

const product = computed(() => data.value?.product ?? null)
const related = computed(() => data.value?.related ?? [])

const gallery = computed(() => {
    const images = [product.value?.featured_img, ...(product.value?.gallery ?? [])]
    return images.filter((image): image is string => Boolean(image))
})

const activeImage = ref(gallery.value[0] ?? '')

// Navigating between two product pages reuses the component, so the selected
// image has to follow the new product.
watch(gallery, (images) => { activeImage.value = images[0] ?? '' })

const attributes = computed(() => {
    const raw = product.value?.attributes

    if (!raw) return []

    // Attributes were historically stored as a plain object and are now a
    // list of {key, value}; both shapes have to render.
    if (Array.isArray(raw)) return raw.filter(item => item?.key)

    return Object.entries(raw as Record<string, string>).map(([key, value]) => ({ key, value }))
})

const phone = computed(() => get('footer.contact.phone', ''))

const crumbs = computed(() => {
    const items = [
        { title: 'Home', to: '/' },
        { title: 'Products', to: '/products' },
    ]

    if (product.value?.category) {
        items.push({
            title: product.value.category.name,
            to: `/categories/${product.value.category.slug}`,
        })
    }

    items.push({ title: product.value?.name ?? '', to: `/products/${slug.value}` })

    return items
})

const origin = computed(() => String(config.public.siteUrl ?? ''))

usePageSeo({
    title: product.value?.meta_title || product.value?.name,
    description: product.value?.meta_description || product.value?.description,
    image: product.value?.featured_img ?? undefined,
    path: `/products/${slug.value}`,
    type: 'product',
    jsonLd: [
        {
            '@context': 'https://schema.org',
            '@type': 'Product',
            name: product.value?.name,
            alternateName: product.value?.name,
            sku: product.value?.pid,
            description: product.value?.description,
            image: gallery.value,
            brand: product.value?.brand
                ? { '@type': 'Brand', name: product.value.brand.name }
                : undefined,
            category: product.value?.category?.name,
        },
        buildBreadcrumbJsonLd(crumbs.value, origin.value),
    ],
})
</script>
