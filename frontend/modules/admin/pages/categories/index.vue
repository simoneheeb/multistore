<template>
    <DashboardPage
        title="Categories"
        description="Manage the category tree here.">
        <template #actions>
            <UiTabs v-model="view" :tabs="viewTabs" class="w-auto" />
            <UiButton icon="plus" @click="openCreate()">New category</UiButton>
        </template>

        <!-- Tree view: the real shape of the data, so a wrong parent is
             obvious at a glance. -->
        <section v-if="view === 'tree'" class="rounded-[var(--radius-base)] border border-border bg-surface p-4">
            <div v-if="!catalog.categoryTree.length" class="py-6">
                <UiEmpty title="No categories yet" />
            </div>

            <CategoryTreeNodeRow
                v-for="node in catalog.categoryTree"
                :key="node.id ?? node.slug"
                :node="node"
                :depth="0"
                @edit="openEdit"
                @add-child="openCreate"
                @remove="confirmDelete" />
        </section>

        <!-- Table view: paginated, better for bulk scanning. -->
        <UiDataTable
            v-else
            :columns="columns"
            :items="catalog.categories"
            :meta="catalog.categoriesMeta"
            :loading="catalog.loading.categories"
            empty-title="No categories yet"
            @page="catalog.fetchCategories($event)">
            <template #cell-name="{ item }">
                <div class="min-w-0">
                    <p class="truncate text-xs font-medium text-heading">
                        <span v-if="(item as any).depth" class="text-muted">{{ '— '.repeat((item as any).depth) }}</span>
                        {{ (item as any).name }}
                    </p>
                    <p class="truncate text-[0.625rem] text-muted">{{ (item as any).slug }}</p>
                </div>
            </template>

            <template #cell-parent="{ item }">
                <span class="text-xs text-muted">{{ (item as any).parent?.name ?? '—' }}</span>
            </template>

            <template #cell-brand="{ item }">
                <span class="text-xs text-muted">{{ (item as any).brand?.name ?? '—' }}</span>
            </template>

            <template #cell-products_length="{ item }">
                <span class="text-xs text-muted">{{ formatNumber((item as any).products_length ?? 0) }}</span>
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
            :title="editing ? 'Edit category' : 'New category'"
            description="Choose a parent to place this category in the tree."
            size="lg"
            :persistent="catalog.loading.saving">
            <CategoryForm
                :category="editing"
                :saving="catalog.loading.saving"
                @submit="save"
                @cancel="formOpen = false" />
        </UiModal>

        <UiModal v-model="deleteOpen" title="Delete category" size="sm">
            <p class="text-sm leading-7 text-body">
                Deleting “{{ pendingDelete?.name }}” also deletes its subcategories and their products.
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
import type { CategoryInterface, CategoryTreeNode, DataTableColumn } from '~/types/interfaces'

definePageMeta({ layout: 'dashboard' })

const catalog = useCatalogStore()

const view = ref<'tree' | 'table'>('tree')
const formOpen = ref(false)
const deleteOpen = ref(false)
const editing = ref<CategoryInterface | null>(null)
const pendingDelete = ref<CategoryInterface | CategoryTreeNode | null>(null)

const viewTabs = [
    { value: 'tree', label: 'Tree', icon: 'layers' },
    { value: 'table', label: 'Table', icon: 'grid' },
]

const columns: DataTableColumn[] = [
    { title: 'Name', key: 'name' },
    { title: 'Parent', key: 'parent' },
    { title: 'Brand', key: 'brand' },
    { title: 'Products', key: 'products_length', width: '6rem' },
    { title: 'Status', key: 'is_active', width: '7rem' },
    { title: '', key: 'actions', align: 'end', width: '6rem' },
]

onMounted(async () => {
    await Promise.all([
        catalog.fetchCategoryTree(),
        catalog.fetchCategories(),
        catalog.fetchAllBrands(),
    ])
})

/**
 * Creating from a tree row pre-selects that row as the parent, which is the
 * fastest way to grow a branch.
 */
const openCreate = (parent?: CategoryTreeNode) => {
    editing.value = parent
        ? ({ parent_id: parent.id, brand_id: undefined } as unknown as CategoryInterface)
        : null

    formOpen.value = true
}

const openEdit = async (category: CategoryInterface | CategoryTreeNode) => {
    const id = (category as CategoryInterface).id
    editing.value = id ? (await AdminService.categories.find(id)) ?? (category as CategoryInterface) : (category as CategoryInterface)
    formOpen.value = true
}

const save = async (payload: FormData) => {
    // A pre-filled parent has no id of its own, so it must not be treated as
    // an edit of an existing record.
    const id = editing.value?.id
    const ok = await catalog.saveCategory(payload, id)
    if (ok) formOpen.value = false
}

const confirmDelete = (category: CategoryInterface | CategoryTreeNode) => {
    pendingDelete.value = category
    deleteOpen.value = true
}

const remove = async () => {
    const id = (pendingDelete.value as CategoryInterface)?.id
    if (!id) return

    await catalog.deleteCategory(id)
    deleteOpen.value = false
    pendingDelete.value = null
}

usePageSeo({ title: 'Manage categories', noindex: true })
</script>
