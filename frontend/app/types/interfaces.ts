/* -------------------------------------------------------------------------
 * Domain types shared by the client and admin modules.
 *
 * The site is single-locale English. An earlier revision carried a second
 * Persian label on every entity (`name`, `description`); those columns
 * were dropped when the site moved to English only.
 * ---------------------------------------------------------------------- */

export interface BrandInterface {
    id?: string
    name: string
    slug: string
    description?: string
    meta_title?: string
    meta_description?: string
    logo?: string | null
    is_active?: boolean
    is_new?: boolean
    order?: number
    categories_length?: number
    products_length?: number
    categories?: CategoryTreeNode[]
    root_categories?: CategoryTreeNode[]
}

export interface CategoryInterface {
    id?: string
    parent_id?: string | null
    brand_id?: string
    name: string
    slug: string
    description?: string
    meta_title?: string
    meta_description?: string
    logo?: string | null
    depth?: number
    order?: number
    is_active?: boolean
    is_new?: boolean
    products_length?: number
    parent?: Pick<CategoryInterface, 'name' | 'slug'> | null
    brand?: BrandInterface
    children?: CategoryInterface[]
    products?: ProductInterface[]
}

/** Lightweight node used by the navigation menu and the admin tree picker. */
export interface CategoryTreeNode {
    id?: string
    parent_id?: string | null
    name: string
    slug: string
    depth: number
    order?: number
    is_active?: boolean
    products_length?: number
    children?: CategoryTreeNode[]
}

/** Flat, depth-annotated option for a <select>. */
export interface CategoryOption {
    id: string
    name: string
    slug: string
    depth: number
}

export interface ProductAttribute {
    key: string
    value: string
}

export interface ProductInterface {
    id?: string
    category_id?: string
    brand_id?: string
    name: string
    slug: string
    pid?: string
    description?: string
    meta_title?: string
    meta_description?: string
    attributes?: ProductAttribute[]
    featured_img?: string | null
    gallery?: string[]
    is_active?: boolean
    is_new?: boolean
    created_at?: string
    updated_at?: string
    category?: CategoryInterface
    brand?: BrandInterface
}

/* ------------------------------------------------------------------ paging */

export interface PageMetaInterface {
    current_page: number
    from: number | null
    to: number | null
    last_page: number
    per_page: number
    total: number
    path: string
    next_page_url: string | null
    prev_page_url: string | null
}

export interface ProductQuery {
    page?: number
    perPage?: number
    all?: boolean
    latest?: boolean
    featured?: boolean
    limit?: number
    category_id?: string
    brand_id?: string
    is_new?: boolean
    search?: string
    sort?: 'latest' | 'oldest' | 'name'
}

/* -------------------------------------------------------------------- auth */

export interface AuthCredentials {
    email: string
    password: string
}

export interface AuthUser {
    id?: string
    name?: string
    email?: string
    mobile?: string
    isActive?: boolean
    isAdmin?: boolean
}

/* -------------------------------------------------------------------- misc */

export interface BreadcrumbItem {
    title: string
    subtitle?: string
    to?: string
}

export interface DataTableColumn {
    title: string
    key: string
    align?: 'start' | 'center' | 'end'
    width?: string
}

/** Envelope every API endpoint answers with. */
export interface ApiEnvelope<T> {
    data: T
    meta?: PageMetaInterface
    message?: string
    status?: boolean
    [key: string]: unknown
}
