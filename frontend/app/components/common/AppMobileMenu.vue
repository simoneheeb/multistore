<template>
    <ClientOnly>
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="opacity-0">
                <div v-if="modelValue" class="fixed inset-0 z-[80] bg-black/45 lg:hidden" @click.self="close" />
            </Transition>

            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 translate-y-2"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="opacity-0 translate-y-2">
                <aside
                    v-if="modelValue"
                    class="fixed inset-y-0 end-0 z-[85] flex w-[85vw] max-w-sm flex-col border-s border-border bg-surface lg:hidden"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Mobile menu">
                    <header class="flex items-center justify-between border-b border-border px-4 py-3">
                        <span class="text-sm font-bold text-heading">Menu</span>

                        <button
                            type="button"
                            class="rounded p-1 text-muted transition-colors hover:bg-surface-muted hover:text-heading"
                            aria-label="Close"
                            @click="close">
                            <UiIcon name="close" :size="18" />
                        </button>
                    </header>

                    <nav class="flex-1 overflow-y-auto px-3 py-3">
                        <ul class="flex flex-col gap-0.5">
                            <li v-for="item in menuItems" :key="item.url ?? item.label">
                                <AppLink
                                    :to="item.url"
                                    class="flex items-baseline gap-2 rounded-[var(--radius-base)] px-3 py-2.5 text-sm text-body transition-colors hover:bg-surface-muted">
                                    {{ item.label }}
                                </AppLink>
                            </li>
                        </ul>

                        <!--
                            Category tree, collapsible one level deep. Deeper
                            nodes stay reachable from the category page itself,
                            which is a better place for a wide branch than a
                            drawer on a phone.
                        -->
                        <section v-if="categoryTree.length" class="mt-4">
                            <p class="px-3 pb-1 text-[0.6875rem] font-medium uppercase tracking-wider text-muted">
                                Categories
                            </p>

                            <ul class="flex flex-col">
                                <li v-for="node in categoryTree" :key="node.slug" class="border-b border-border last:border-0">
                                    <div class="flex items-stretch">
                                        <NuxtLink
                                            :to="`/categories/${node.slug}`"
                                            class="flex-1 px-3 py-2.5 text-sm text-body transition-colors hover:text-primary">
                                            {{ node.name }}
                                        </NuxtLink>

                                        <button
                                            v-if="node.children?.length"
                                            type="button"
                                            class="px-3 text-muted transition-colors hover:text-primary"
                                            :aria-expanded="expanded === node.slug"
                                            :aria-label="`Subcategories of ${node.name}`"
                                            @click="expanded = expanded === node.slug ? null : node.slug">
                                            <UiIcon
                                                name="chevron-down"
                                                :size="16"
                                                :class="['transition-transform', expanded === node.slug && 'rotate-180']" />
                                        </button>
                                    </div>

                                    <ul v-if="expanded === node.slug" class="pb-2 ps-4">
                                        <li v-for="child in node.children" :key="child.slug">
                                            <NuxtLink
                                                :to="`/categories/${node.slug}/${child.slug}`"
                                                class="block rounded px-3 py-2 text-[0.8125rem] text-muted transition-colors hover:text-primary">
                                                {{ child.name }}
                                            </NuxtLink>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </section>

                        <section v-if="brands.length" class="mt-4">
                            <p class="px-3 pb-1 text-[0.6875rem] font-medium uppercase tracking-wider text-muted">
                                Brands
                            </p>

                            <ul class="flex flex-col">
                                <li v-for="brand in brands.slice(0, 12)" :key="brand.slug">
                                    <NuxtLink
                                        :to="`/brands/${brand.slug}`"
                                        class="block rounded px-3 py-2 text-[0.8125rem] text-body transition-colors hover:text-primary">
                                        {{ brand.name }}
                                    </NuxtLink>
                                </li>
                            </ul>
                        </section>
                    </nav>
                </aside>
            </Transition>
        </Teleport>
    </ClientOnly>
</template>

<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import type { BrandInterface, CategoryTreeNode } from '~/types/interfaces'

const props = defineProps<{
    modelValue: boolean
    menuItems: Array<{ label?: string; url?: string }>
    categoryTree: CategoryTreeNode[]
    brands: BrandInterface[]
}>()

const emit = defineEmits<{ 'update:modelValue': [boolean] }>()

const expanded = ref<string | null>(null)

const close = () => emit('update:modelValue', false)

// Lock the page behind the drawer while it is open, so a scroll gesture moves
// the menu rather than the content underneath it.
watch(() => props.modelValue, (open) => {
    if (import.meta.client) document.body.style.overflow = open ? 'hidden' : ''
})

onBeforeUnmount(() => {
    if (import.meta.client) document.body.style.overflow = ''
})
</script>
