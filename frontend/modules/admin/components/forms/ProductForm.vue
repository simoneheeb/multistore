<template>
    <form class="flex flex-col gap-5" @submit.prevent="submit">
        <div class="grid gap-4 sm:grid-cols-2">
            <UiSelect
                v-model="form.brand_id"
                label="Brand"
                placeholder="Select a brand"
                required
                :options="brandOptions" />

            <UiCategoryTreeSelect
                v-model="form.category_id"
                label="Category"
                placeholder="Select a category"
                required
                :options="catalog.categoryOptions"
                hint="Attach the product to the most specific subcategory." />

            <UiInput
                v-model="form.name"
                label="Name"
                required
                :rules="[required('Name')]" />

            <UiInput
                v-model="form.slug"
                label="Slug"
                hint="Leave empty to generate it automatically." />
        </div>

        <UiTextarea
            v-model="form.description"
            label="Description"
            :rows="6"
            required
            :rules="[required('Description')]" />

        <UiFilePicker
            v-model="featuredFiles"
            label="Featured image"
            :existing="existingFeatured"
            :max-size="5"
            :max-files="1"
            hint="The main product image — jpg, png or webp, up to 5 MB" />

        <UiFilePicker
            v-model="galleryFiles"
            label="Gallery"
            :existing="existingGallery"
            accept="image/png,image/jpeg,image/webp,video/mp4"
            multiple
            :max-size="20"
            :max-files="12"
            hint="Choosing new files replaces the entire existing gallery." />

        <!-- Attributes -->
        <UiRepeater
            v-model="attributes"
            label="Specifications"
            hint="One row per attribute: a name and a value."
            add-label="Add attribute"
            :factory="() => ({ key: '', value: '' })"
            :max="40">
            <template #default="{ item, update }">
                <div class="grid gap-3 sm:grid-cols-2">
                    <UiInput
                        :model-value="item.key"
                        label="Title"
                        @update:model-value="update({ key: $event })" />

                    <UiInput
                        :model-value="item.value"
                        label="Value"
                        @update:model-value="update({ value: $event })" />
                </div>
            </template>
        </UiRepeater>

        <SeoFields v-model:meta-title="form.meta_title" v-model:meta-description="form.meta_description" />

        <div class="flex flex-wrap gap-5 border-t border-border pt-4">
            <UiSwitch v-model="form.is_active" label="Active" description="Inactive products are hidden from the site." />
            <UiSwitch v-model="form.is_new" label="New product" description="Appears in the “Featured products” strip on the home page." />
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
            <UiButton variant="ghost" @click="$emit('cancel')">Cancel</UiButton>
            <UiButton type="submit" icon="save" :loading="saving">
                {{ product ? 'Save changes' : 'Create product' }}
            </UiButton>
        </div>
    </form>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { useCatalogStore } from '~~/modules/admin/stores/catalog'
import { required } from '~/helpers/rules'
import type { ProductAttribute, ProductInterface } from '~/types/interfaces'

const props = defineProps<{
    product?: ProductInterface | null
    saving?: boolean
}>()

const emit = defineEmits<{ submit: [FormData]; cancel: [] }>()

const catalog = useCatalogStore()
const toast = useToast()

const blank = () => ({
    brand_id: '',
    category_id: '',
    name: '',
    slug: '',
    description: '',
    meta_title: '',
    meta_description: '',
    is_active: true,
    is_new: false,
})

const form = reactive(blank())
const attributes = ref<ProductAttribute[]>([])
const featuredFiles = ref<File[]>([])
const galleryFiles = ref<File[]>([])

const existingFeatured = computed(() => (props.product?.featured_img ? [props.product.featured_img] : []))
const existingGallery = computed(() => props.product?.gallery ?? [])

const brandOptions = computed(() =>
    catalog.allBrands.map(brand => ({ value: brand.id ?? '', label: `${brand.name} — ${brand.name}` })),
)

watch(() => props.product, async (product) => {
    Object.assign(form, blank())
    attributes.value = []
    featuredFiles.value = []
    galleryFiles.value = []

    await Promise.all([catalog.fetchAllBrands(), catalog.fetchCategoryOptions()])

    if (product) {
        Object.assign(form, {
            brand_id: product.brand_id ?? product.brand?.id ?? '',
            category_id: product.category_id ?? product.category?.id ?? '',
            name: product.name ?? '',
            slug: product.slug ?? '',
            description: product.description ?? '',
            meta_title: product.meta_title ?? '',
            meta_description: product.meta_description ?? '',
            is_active: product.is_active ?? true,
            is_new: product.is_new ?? false,
        })

        attributes.value = normaliseAttributes(product.attributes)
    }
}, { immediate: true })

/**
 * Attributes are stored as a list of {key, value}, but older records hold a
 * plain object. Both are normalised into the list the editor works with.
 */
function normaliseAttributes(raw: unknown): ProductAttribute[] {
    if (!raw) return []

    if (Array.isArray(raw)) {
        return raw
            .filter(item => item && typeof item === 'object')
            .map(item => ({ key: String((item as any).key ?? ''), value: String((item as any).value ?? '') }))
    }

    return Object.entries(raw as Record<string, unknown>)
        .map(([key, value]) => ({ key, value: String(value) }))
}

const submit = () => {
    if (!form.brand_id || !form.category_id) {
        toast.error('Please choose a brand and a category.')
        return
    }

    if (!form.name || !form.name || !form.description || !form.description) {
        toast.error('Name and description are required.')
        return
    }

    if (!props.product && !featuredFiles.value.length) {
        toast.error('A featured image is required.')
        return
    }

    const payload = new FormData()

    Object.entries(form).forEach(([key, value]) => {
        if (value === '' || value === null || value === undefined) return
        payload.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : String(value))
    })

    // Rows left blank in the repeater are dropped rather than failing
    // validation on the server.
    const filled = attributes.value.filter(item => item.key.trim() && item.value.trim())
    if (filled.length) payload.append('attributes', JSON.stringify(filled))

    if (featuredFiles.value[0]) payload.append('featured_img', featuredFiles.value[0])

    galleryFiles.value.forEach(file => payload.append('gallery[]', file))

    emit('submit', payload)
}
</script>
