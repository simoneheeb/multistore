<template>
    <DashboardPage
        title="Products"
        description="Add, edit and remove products.">
        <template #actions>
            <UiButton icon="plus" @click="openCreate">New product</UiButton>
        </template>

        <UiDataTable
            :columns="columns"
            :items="catalog.products"
            :meta="catalog.productsMeta"
            :loading="catalog.loading.products"
            empty-title="No products yet"
            empty-description="Use “New product” to add the first one."
            @page="catalog.fetchProducts($event, search)">
            <template #toolbar>
                <form class="flex items-center gap-2" @submit.prevent="catalog.fetchProducts(1, search)">
                    <UiInput v-model="search" icon="search" placeholder="Search products…" class="w-56" />
                    <UiButton type="submit" size="sm" variant="outline">Search</UiButton>
                </form>
            </template>

            <template #cell-featured_img="{ item }">
                <UiImage :src="(item as any).featured_img" :alt="(item as any).name" ratio="square" class="h-10 w-10" />
            </template>

            <template #cell-name="{ item }">
                <div class="min-w-0">
                    <p class="truncate text-xs font-medium text-heading">{{ (item as any).name }}</p>
                    <p class="truncate text-[0.625rem] text-muted">{{ (item as any).slug }}</p>
                </div>
            </template>

            <template #cell-category="{ item }">
                <span class="text-xs text-muted">{{ (item as any).category?.name ?? '—' }}</span>
            </template>

            <template #cell-brand="{ item }">
                <span class="text-xs text-muted">{{ (item as any).brand?.name ?? '—' }}</span>
            </template>

            <template #cell-pid="{ item }">
                <span class="text-[0.625rem] text-muted">{{ (item as any).pid ?? '—' }}</span>
            </template>

            <template #cell-is_active="{ item }">
                <div class="flex items-center justify-center gap-1">
                    <UiBadge :tone="(item as any).is_active ? 'success' : 'neutral'">
                        {{ (item as any).is_active ? 'Active' : 'Inactive' }}
                    </UiBadge>
                    <UiBadge v-if="(item as any).is_new" tone="primary">New</UiBadge>
                </div>
            </template>

            <template #cell-actions="{ item }">
                <div class="flex items-center justify-end gap-1">
                    <NuxtLink
                        :to="`/products/${(item as any).slug}`"
                        target="_blank"
                        class="rounded p-1.5 text-muted transition-colors hover:bg-surface-muted hover:text-primary"
                        aria-label="View on site">
                        <UiIcon name="eye" :size="16" />
                    </NuxtLink>

                    <button
                        type="button"
                        class="rounded p-1.5 text-muted transition-colors hover:bg-surface-muted hover:text-primary"
                        aria-label="Edit"
                        @click="openEdit(item as any)">
                        <UiIcon name="edit" :size="16" />
                    </button>

                    <button
                        type="button"
                        class="rounded p-1.5 text-danger transition-colors hover:bg-danger/10"
                        aria-label="Delete"
                        @click="confirmDelete(item as any)">
                        <UiIcon name="trash" :size="16" />
                    </button>
                </div>
            </template>
        </UiDataTable>

        <UiModal
            v-model="formOpen"
            :title="editing ? 'Edit product' : 'New product'"
            description="Complete every field so the product page renders properly."
            size="xl"
            :persistent="catalog.loading.saving">
            <ProductForm
                :product="editing"
                :saving="catalog.loading.saving"
                @submit="save"
                @cancel="formOpen = false" />
        </UiModal>

        <UiModal v-model="deleteOpen" title="Delete product" size="sm">
            <p class="text-sm leading-7 text-body">
                “{{ pendingDelete?.name }}” and its images will be deleted. This cannot be undone.
            </p>

            <template #footer>
                <UiButton variant="ghost" @click="deleteOpen = false">Cancel</UiButton>
                <UiButton variant="danger" icon="trash" @click="remove">Delete</UiButton>
            </template>
        </UiModal>
    </DashboardPage>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { definePageMeta } from '#imports'
import { AdminService } from '~~/modules/admin/services/AdminService'
import { useCatalogStore } from '~~/modules/admin/stores/catalog'
import type { DataTableColumn, ProductInterface } from '~/types/interfaces'

definePageMeta({ layout: 'dashboard' })

const catalog = useCatalogStore()

const search = ref('')
const formOpen = ref(false)
const deleteOpen = ref(false)
const editing = ref<ProductInterface | null>(null)
const pendingDelete = ref<ProductInterface | null>(null)

const columns: DataTableColumn[] = [
    { title: 'Image', key: 'featured_img', width: '4.5rem' },
    { title: 'Name', key: 'name' },
    { title: 'Category', key: 'category' },
    { title: 'Brand', key: 'brand' },
    { title: 'SKU', key: 'pid', width: '8rem' },
    { title: 'Status', key: 'is_active', align: 'center', width: '9rem' },
    { title: '', key: 'actions', align: 'end', width: '7rem' },
]

onMounted(() => catalog.fetchProducts())

const openCreate = () => {
    editing.value = null
    formOpen.value = true
}

const openEdit = async (product: ProductInterface) => {
    // The listing hides ids from anonymous callers and trims relations, so the
    // full record is re-fetched before editing.
    editing.value = product.id ? (await AdminService.products.find(product.id)) ?? product : product
    formOpen.value = true
}

const save = async (payload: FormData) => {
    const ok = await catalog.saveProduct(payload, editing.value?.id)
    if (ok) formOpen.value = false
}

const confirmDelete = (product: ProductInterface) => {
    pendingDelete.value = product
    deleteOpen.value = true
}

const remove = async () => {
    if (!pendingDelete.value?.id) return

    await catalog.deleteProduct(pendingDelete.value.id)
    deleteOpen.value = false
    pendingDelete.value = null
}

usePageSeo({ title: 'Manage products', noindex: true })
</script>
