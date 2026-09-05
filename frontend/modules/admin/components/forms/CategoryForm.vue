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
                v-model="form.parent_id"
                label="Parent category"
                :options="catalog.categoryOptions"
                hint="Leave empty for a top-level category. The tree has no depth limit." />

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

        <UiTextarea v-model="form.description" label="Description" :rows="4" />

        <UiFilePicker
            v-model="logoFiles"
            label="Category image"
            :existing="existingLogo"
            accept="image/png,image/jpeg,image/webp,image/svg+xml"
            :max-size="2"
            :max-files="1"
            hint="Optional — shown on the category card on the home page." />

        <SeoFields v-model:meta-title="form.meta_title" v-model:meta-description="form.meta_description" />

        <div class="flex flex-wrap items-start gap-5 border-t border-border pt-4">
            <UiSwitch v-model="form.is_active" label="Active" />
            <UiSwitch v-model="form.is_new" label="New category flag" />

            <UiInput v-model="form.order" label="Order" type="number" class="w-32" />
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
            <UiButton variant="ghost" @click="$emit('cancel')">Cancel</UiButton>
            <UiButton type="submit" icon="save" :loading="saving">
                {{ category ? 'Save changes' : 'Create category' }}
            </UiButton>
        </div>
    </form>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { useCatalogStore } from '~~/modules/admin/stores/catalog'
import { required } from '~/helpers/rules'
import type { CategoryInterface } from '~/types/interfaces'

const props = defineProps<{
    category?: CategoryInterface | null
    saving?: boolean
}>()

const emit = defineEmits<{ submit: [FormData]; cancel: [] }>()

const catalog = useCatalogStore()
const toast = useToast()

const blank = () => ({
    brand_id: '',
    parent_id: null as string | null,
    name: '',
    slug: '',
    description: '',
    meta_title: '',
    meta_description: '',
    is_active: true,
    is_new: false,
    order: 0,
})

const form = reactive(blank())
const logoFiles = ref<File[]>([])

const existingLogo = computed(() => (props.category?.logo ? [props.category.logo] : []))

const brandOptions = computed(() =>
    catalog.allBrands.map(brand => ({ value: brand.id ?? '', label: `${brand.name} — ${brand.name}` })),
)

watch(() => props.category, async (category) => {
    Object.assign(form, blank())
    logoFiles.value = []

    await catalog.fetchAllBrands()

    if (category) {
        Object.assign(form, {
            brand_id: category.brand_id ?? category.brand?.id ?? '',
            parent_id: category.parent_id ?? null,
            name: category.name ?? '',
            slug: category.slug ?? '',
            description: category.description ?? '',
            meta_title: category.meta_title ?? '',
            meta_description: category.meta_description ?? '',
            is_active: category.is_active ?? true,
            is_new: category.is_new ?? false,
            order: category.order ?? 0,
        })
    }

    // Excluding the edited node removes it and its descendants from the
    // parent list, which is what prevents a cycle in the tree.
    await catalog.fetchCategoryOptions(category?.id)
}, { immediate: true })

const submit = () => {
    if (!form.brand_id) {
        toast.error('Please choose a brand.')
        return
    }

    if (!form.name || !form.name) {
        toast.error('A name is required.')
        return
    }

    const payload = new FormData()

    Object.entries(form).forEach(([key, value]) => {
        if (value === '' || value === null || value === undefined) return
        payload.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : String(value))
    })

    if (logoFiles.value[0]) payload.append('logo', logoFiles.value[0])

    emit('submit', payload)
}
</script>
