/* -------------------------------------------------------------------------
 * Site settings.
 *
 * Mirrors Modules/Settings/config/defaults.php on the backend one-to-one.
 * Every group here is editable from the admin panel, so nothing on the
 * public site is hard-coded in a template.
 *
 * The site is single-locale English, so each label is one field. An earlier
 * revision carried `*_en` / `*_fa` pairs for a bilingual layout; those were
 * collapsed when the site moved to English only.
 * ---------------------------------------------------------------------- */

export interface NavLink {
    label?: string
    url?: string
}

export interface IdentitySettings {
    site_name: string
    tagline: string
    description: string
    logo: string
    logo_dark: string
    logo_footer: string
    favicon: string
    logo_width: number
    logo_height: number
}

export interface HeaderSettings {
    sticky: boolean
    show_search: boolean
    show_category_menu: boolean
    show_brand_menu: boolean
    topbar: { enabled: boolean; text: string; phone: string }
    menu_items: NavLink[]
    cta: { enabled: boolean; label: string; url: string }
}

export interface FooterSettings {
    about: { title: string; text: string }
    columns: Array<{ title: string; links: NavLink[] }>
    contact: {
        title: string
        address: string
        phone: string
        mobile: string
        email: string
        working_hours: string
    }
    copyright: string
    show_newsletter: boolean
}

export interface SocialSettings {
    items: Array<{ platform: string; label: string; url: string }>
}

export interface HeroSlide {
    id?: number
    /** Small uppercase line above the headline. */
    eyebrow: string
    title: string
    description: string
    image: string
    cta_label: string
    cta_url: string
}

export interface HeroSettings {
    autoplay: boolean
    interval: number
    slides: HeroSlide[]
}

export interface FeatureSettings {
    enabled: boolean
    eyebrow: string
    title: string
    items: Array<{ icon: string; title: string; description: string }>
}

export interface PromoSettings {
    enabled: boolean
    eyebrow: string
    title: string
    description: string
    badge: string
    image: string
    cta_label: string
    cta_url: string
}

export interface TestimonialSettings {
    enabled: boolean
    eyebrow: string
    title: string
    items: Array<{ name: string; role: string; avatar: string; rating: number; text: string }>
}

export interface ArticleSettings {
    enabled: boolean
    eyebrow: string
    title: string
    items: Array<{ title: string; excerpt: string; image: string; url: string; date: string }>
}

export interface NewsletterSettings {
    enabled: boolean
    eyebrow: string
    title: string
    description: string
    placeholder: string
    button_label: string
}

export interface AboutPageSettings {
    title: string
    subtitle: string
    cover: string
    body: string
    stats: Array<{ value: string; label: string }>
    sections: Array<{ title: string; text: string; image: string }>
    meta_title: string
    meta_description: string
}

export interface ContactPageSettings {
    title: string
    subtitle: string
    cover: string
    intro: string
    address: string
    phones: string[]
    emails: string[]
    working_hours: string
    map_embed: string
    form_enabled: boolean
    meta_title: string
    meta_description: string
}

export interface SeoSettings {
    default_title: string
    title_template: string
    default_description: string
    default_keywords: string
    og_image: string
    twitter_handle: string
    robots_index: boolean
    google_site_verification: string
    organization: {
        name: string
        legal_name: string
        logo: string
        phone: string
        address: string
    }
}

export interface HomeSectionsSettings {
    sections: Array<{ key: string; enabled: boolean }>
}

export interface ThemeSettings {
    primary: string
    accent: string
    radius: 'none' | 'sm' | 'md' | 'lg'
    default_mode: 'light' | 'dark' | 'system'
    container: 'narrow' | 'wide' | 'full'
}

export interface SiteSettings {
    identity: IdentitySettings
    header: HeaderSettings
    footer: FooterSettings
    social: SocialSettings
    hero: HeroSettings
    features: FeatureSettings
    promo: PromoSettings
    testimonials: TestimonialSettings
    articles: ArticleSettings
    newsletter: NewsletterSettings
    about_page: AboutPageSettings
    contact_page: ContactPageSettings
    seo: SeoSettings
    home_sections: HomeSectionsSettings
    theme: ThemeSettings
}

export type SettingsKey = keyof SiteSettings
