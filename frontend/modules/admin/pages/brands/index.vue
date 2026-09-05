<template>
    <DashboardPage
        title="Brands"
        description="Manage the brands carried by the store.">
        <template #actions>
            <UiButton icon="plus" @click="openCreate">New brand</UiButton>
        </template>

        <UiDataTable
            :columns="columns"
            :items="catalog.brands"
            :meta="catalog.brandsMeta"
            :loading="catalog.loading.brands"
            empty-title="No brands yet"
            empty-description="Use “New brand” to add the first one."
            @page="catalog.fetchBrands($event)">
            <template #cell-logo="{ item }">
                <UiImage :src="(item as any).logo" :alt="(item as any).name" fit="contain" ratio="square" class="h-10 w-10" />
            </template>

            <template #cell-name="{ item }">
                <div class="min-w-0">
                    <p class="truncate text-xs font-medium text-heading">{{ (item as any).name }}</p>
                    <p class="truncate text-[0.625rem] text-muted">{{ (item as any).slug }}</p>
                </div>
            </template>

            <template #cell-counts="{ item }">
                <span class="text-xs text-muted">
                    {{ formatNumber((item as any).categories_length ?? 0) }} categories /
                    {{ formatNumber((item as any).products_length ?? 0) }} products
                </span>
            </template>

            <template #cell-is_active="{ item }">
                <UiBadge :tone="(item as any).is_active ? 'success' : 'neutral'">
                    {{ (item as any).is_active ? 'Active' : 'Inactive' }}
                </UiBadge>
            </template>

            <template #cell-actions="{ item }">
                <div class="flex items-center justify-end gap-1">
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
            :title="editing ? 'Edit brand' : 'New brand'"
            description="Fill in the name and description."
            size="lg"
            :persistent="catalog.loading.saving">
            <BrandForm
                :brand="editing"
                :saving="catalog.loading.saving"
                @submit="save"
                @cancel="formOpen = false" />
        </UiModal>

        <UiModal v-model="deleteOpen" title="Delete brand" size="sm">
            <p class="text-sm leading-7 text-body">
                Deleting “{{ pendingDelete?.name }}” also deletes every category and product that belongs to it.
                This cannot be undone.
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
import { formatNumber } from '~/utils/format'
import type { BrandInterface, DataTableColumn } from '~/types/interfaces'

definePageMeta({ layout: 'dashboard' })

const catalog = useCatalogStore()

const formOpen = ref(false)
const deleteOpen = ref(false)
const editing = ref<BrandInterface | null>(null)
const pendingDelete = ref<BrandInterface | null>(null)

const columns: DataTableColumn[] = [
    { title: 'Logo', key: 'logo', width: '4.5rem' },
    { title: 'Name', key: 'name' },
    { title: 'Content', key: 'counts' },
    { title: 'Status', key: 'is_active', width: '7rem' },
    { title: '', key: 'actions', align: 'end', width: '6rem' },
]

onMounted(() => catalog.fetchBrands())

const openCreate = () => {
    editing.value = null
    formOpen.value = true
}

/**
 * The table row omits fields the list endpoint does not return, so the full
 * record is re-fetched before the form opens.
 */
const openEdit = async (brand: BrandInterface) => {
    editing.value = brand.id ? (await AdminService.brands.find(brand.id)) ?? brand : brand
    formOpen.value = true
}

const save = async (payload: FormData) => {
    const ok = await catalog.saveBrand(payload, editing.value?.id)
    if (ok) formOpen.value = false
}

const confirmDelete = (brand: BrandInterface) => {
    pendingDelete.value = brand
    deleteOpen.value = true
}

const remove = async () => {
    if (!pendingDelete.value?.id) return

    await catalog.deleteBrand(pendingDelete.value.id)
    deleteOpen.value = false
    pendingDelete.value = null
}

usePageSeo({ title: 'Manage brands', noindex: true })
</script>
