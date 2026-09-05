<template>
    <form class="flex flex-col gap-5" @submit.prevent="submit">
        <div class="grid gap-4 sm:grid-cols-2">
            <UiInput
                v-model="form.name"
                label="Name"
                required
                :rules="[required('Name')]" />

            <UiInput
                v-model="form.slug"
                label="Slug"
                hint="Leave empty to generate it from the name." />
        </div>

        <UiTextarea
            v-model="form.description"
            label="Description"
            :rows="5"
            required
            :rules="[required('Description')]" />

        <UiFilePicker
            v-model="logoFiles"
            label="Logo"
            :existing="existingLogo"
            accept="image/png,image/jpeg,image/webp,image/svg+xml"
            :max-size="2"
            :max-files="1"
            hint="Allowed formats: jpg, png, webp, svg — up to 2 MB" />

        <SeoFields v-model:meta-title="form.meta_title" v-model:meta-description="form.meta_description" />

        <div class="flex flex-wrap gap-5 border-t border-border pt-4">
            <UiSwitch v-model="form.is_active" label="Active" description="Inactive brands are hidden from the site." />
            <UiSwitch v-model="form.is_new" label="New brand" description="Shows a “New” badge next to the brand." />

            <UiInput
                v-model="form.order"
                label="Display order"
                type="number"
                class="w-32" />
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
            <UiButton variant="ghost" @click="$emit('cancel')">Cancel</UiButton>
            <UiButton type="submit" icon="save" :loading="saving">
                {{ brand ? 'Save changes' : 'Create brand' }}
            </UiButton>
        </div>
    </form>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { required } from '~/helpers/rules'
import type { BrandInterface } from '~/types/interfaces'

const props = defineProps<{
    brand?: BrandInterface | null
    saving?: boolean
}>()

const emit = defineEmits<{ submit: [FormData]; cancel: [] }>()

const toast = useToast()

const blank = () => ({
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

const existingLogo = computed(() => (props.brand?.logo ? [props.brand.logo] : []))

// Re-seed the form whenever a different brand is opened, so the dialog never
// shows the previous record's values.
watch(() => props.brand, (brand) => {
    Object.assign(form, blank())
    logoFiles.value = []

    if (brand) {
        Object.assign(form, {
            name: brand.name ?? '',
            slug: brand.slug ?? '',
            description: brand.description ?? '',
            meta_title: brand.meta_title ?? '',
            meta_description: brand.meta_description ?? '',
            is_active: brand.is_active ?? true,
            is_new: brand.is_new ?? false,
            order: brand.order ?? 0,
        })
    }
}, { immediate: true })

const submit = () => {
    if (!form.name || !form.name || !form.description || !form.description) {
        toast.error('Name and description are required.')
        return
    }

    // A logo is mandatory when creating; on edit, leaving it empty keeps the
    // existing file rather than clearing it.
    if (!props.brand && !logoFiles.value.length) {
        toast.error('A logo is required.')
        return
    }

    const payload = new FormData()

    Object.entries(form).forEach(([key, value]) => {
        if (value === '' || value === null || value === undefined) return
        // Booleans have to travel as 1/0: multipart has no boolean type and
        // the string "false" would validate as true.
        payload.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : String(value))
    })

    if (logoFiles.value[0]) payload.append('logo', logoFiles.value[0])

    emit('submit', payload)
}
</script>
