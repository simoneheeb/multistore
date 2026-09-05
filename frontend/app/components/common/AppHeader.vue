<template>
    <header :class="['z-50 w-full', sticky ? 'sticky top-0' : 'relative']">
        <!-- Announcement bar -->
        <div v-if="topbar.enabled" class="border-b border-border bg-black">
            <div class="site-container flex h-15 items-center justify-center gap-4 text-[0.6875rem]">

                <NuxtLink to="/" class="flex h-12 w-[11rem] items-center p-5" :aria-label="siteName">
                    <UiImage
                        :src="logo"
                        :alt="siteName"
                        ratio="none"
                        fit="contain"
                        :rounded="false"
                        eager
                        class="h-8 w-full"
                    />
                </NuxtLink>

            </div>
        </div>
        <!-- Main bar -->
        <div class="border-b border-border bg-surface backdrop-blur">
            <div class="site-container flex h-16 items-center justify-between gap-4">

                <!-- Desktop navigation -->
                <nav class="hidden items-center gap-0.5 lg:flex" aria-label="Main menu">
                    <AppLink
                        v-for="item in menuItems"
                        :key="item.url ?? item.label"
                        :to="item.url"
                        class="rounded-[var(--radius-base)] px-3 py-2 text-sm font-medium text-body transition-colors hover:bg-surface-muted hover:text-primary"
                        :class="isCurrent(item) ? 'text-primary' : ''">
                        {{ item.label }}
                    </AppLink>

                    <AppMegaMenu
                        v-if="showCategoryMenu && categoryTree.length"
                        label="Categories"
                        :columns="categoryColumns"
                        cta-label="All categories"
                        cta-to="/categories" />

                    <AppMegaMenu
                        v-if="showBrandMenu && brandColumns.length"
                        label="Brands"
                        :columns="brandColumns"
                        cta-label="All brands"
                        cta-to="/brands" />
                </nav>

                <div class="flex items-center gap-1">
                    <button
                        v-if="showSearch"
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-[var(--radius-base)] text-muted transition-colors hover:bg-surface-muted hover:text-primary"
                        aria-label="Search"
                        @click="searchOpen = true">
                        <UiIcon name="search" :size="18" />
                    </button>

                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-[var(--radius-base)] text-muted transition-colors hover:bg-surface-muted hover:text-primary"
                        :aria-label="isDark ? 'Switch to light theme' : 'Switch to dark theme'"
                        @click="toggle">
                        <UiIcon :name="isDark ? 'sun' : 'moon'" :size="18" />
                    </button>

                    <UiButton
                        v-if="cta.enabled"
                        :to="cta.url"
                        size="sm"
                        class="hidden lg:inline-flex">
                        {{ cta.label }}
                    </UiButton>

                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-[var(--radius-base)] text-muted transition-colors hover:bg-surface-muted hover:text-primary lg:hidden"
                        aria-label="Menu"
                        :aria-expanded="drawerOpen"
                        @click="drawerOpen = true">
                        <UiIcon name="menu" :size="20" />
                    </button>
                </div>
            </div>
        </div>
        <div class="h-full border-b border-border bg-surface-muted py-2">
            <div class="site-container flex justify-between">
                <p class="flex min-w-0 items-center truncate text-xs text-body">
                    <span class="truncate">{{ topbar.text }}</span>
                </p>

                <a v-if="topbar.phone" :href="`tel:${topbar.phone}`"
                    class="flex shrink-0 items-center gap-1.5 text-xs text-body transition-colors hover:text-primary">
                    <UiIcon name="phone" :size="13" />
                    <span >{{ topbar.phone }}</span>
                </a>
            </div>
        </div>
        <AppMobileMenu
            v-model="drawerOpen"
            :menu-items="menuItems"
            :category-tree="showCategoryMenu ? categoryTree : []"
            :brands="showBrandMenu ? brands : []" />

        <AppSearchOverlay v-model="searchOpen" />
    </header>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useNavigation } from '~~/app/composables/useNavigation'
import { useSiteSettings } from '~~/app/composables/useSiteSettings'
import { useTheme } from '~~/app/composables/useTheme'

const route = useRoute()
const { get } = useSiteSettings()
const { categoryTree, brands } = useNavigation()
const { isDark, toggle } = useTheme()

const drawerOpen = ref(false)
const searchOpen = ref(false)

// Any navigation should dismiss the overlays; otherwise the drawer stays open
// on top of the page the visitor just asked for.
watch(() => route.fullPath, () => {
    drawerOpen.value = false
    searchOpen.value = false
})

const sticky = computed(() => get('header.sticky', true))
const showSearch = computed(() => get('header.show_search', true))
const showCategoryMenu = computed(() => get('header.show_category_menu', true))
const showBrandMenu = computed(() => get('header.show_brand_menu', true))
const topbar = computed(() => get('header.topbar', { enabled: false, text: '', phone: '' }))
const cta = computed(() => get('header.cta', { enabled: false, label: '', url: '/' }))
const menuItems = computed(() => get<any[]>('header.menu_items', []))

/**
 * A menu entry is "current" when its URL matches the route. The home link
 * matches exactly; every other link also matches its own sub-pages, so
 * /products/some-item still highlights "Products".
 */
const isCurrent = (item: { url?: string }) => {
    const url = item.url ?? ''
    if (!url.startsWith('/')) return false

    return url === '/' ? route.path === '/' : route.path.startsWith(url)
}

const logo = computed(() =>
    isDark.value
        ? get('identity.logo_dark', get('identity.logo', '/assets/images/logo-header.png'))
        : get('identity.logo', '/assets/images/logo-header.png'),
)

const siteName = computed(() => get('identity.site_name', ''))

/**
 * Top-level categories become the mega-menu columns, each listing its own
 * children - two levels is as deep as a dropdown stays usable.
 */
const categoryColumns = computed(() =>
    categoryTree.value.slice(0, 8).map(node => ({
        title: node.name,
        to: `/categories/${node.slug}`,
        links: (node.children ?? []).slice(0, 6).map(child => ({
            title: child.name,
            to: `/categories/${node.slug}/${child.slug}`,
            count: child.products_length,
        })),
    })),
)

/** Brands have no hierarchy, so they are chunked into even columns. */
const brandColumns = computed(() => {
    const items = brands.value.slice(0, 16)
    if (!items.length) return []

    const perColumn = Math.ceil(items.length / Math.min(4, Math.ceil(items.length / 4) || 1))

    const columns: { title: string; to?: string; links: any[] }[] = []

    for (let index = 0; index < items.length; index += perColumn) {
        columns.push({
            title: '',
            links: items.slice(index, index + perColumn).map(brand => ({
                title: brand.name,
                to: `/brands/${brand.slug}`,
                count: brand.categories_length,
            })),
        })
    }

    return columns
})
</script>
