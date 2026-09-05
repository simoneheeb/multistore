<?php

namespace Modules\Settings\Services;

/**
 * Single source of truth for which settings groups exist, what their default
 * shape is, and how an incoming payload for each group is validated.
 *
 * Keeping this in one class means adding a new editable section to the site
 * is a two-step change (default + rules) instead of touching a controller, a
 * request, a service and a seeder.
 */
class SettingsSchema
{
    /**
     * Default value of every group, loaded from the module config.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function defaults(): array
    {
        return config('settings-defaults', []);
    }

    /**
     * Names of every writable settings group.
     *
     * @return list<string>
     */
    public static function groups(): array
    {
        return array_keys(self::defaults());
    }

    public static function isGroup(string $key): bool
    {
        return array_key_exists($key, self::defaults());
    }

    /**
     * Default value for one group, or an empty array for an unknown group.
     *
     * @return array<string, mixed>
     */
    public static function defaultFor(string $key): array
    {
        return self::defaults()[$key] ?? [];
    }

    /**
     * Validation rules per group, keyed by the group name. Rules are written
     * against a payload of the shape { key: <group>, value: {...} }.
     *
     * Only fields whose type actually matters are constrained; free text is
     * left to the generic string/nullable rules so an admin is never blocked
     * from writing ordinary copy.
     *
     * @return array<string, array<string, mixed>>
     */
    /**
     * Validation rules per group, keyed by the group name. Rules are written
     * against a payload of the shape { key: <group>, value: {...} }.
     *
     * Only fields whose type actually matters are constrained; free text is
     * left to the generic string/nullable rules so an admin is never blocked
     * from writing ordinary copy.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function rules(): array
    {
        // Shared fragment: a list of links used by the header menu and by
        // every footer column.
        $linkList = fn (string $path) => [
            $path => ['array', 'max:50'],
            $path.'.*.label' => ['nullable', 'string', 'max:120'],
            $path.'.*.url' => ['nullable', 'string', 'max:255'],
        ];

        return [
            'identity' => [
                'value.site_name' => ['nullable', 'string', 'max:120'],
                'value.tagline' => ['nullable', 'string', 'max:160'],
                'value.description' => ['nullable', 'string', 'max:500'],
                'value.logo' => ['nullable', 'string'],
                'value.logo_dark' => ['nullable', 'string'],
                'value.logo_footer' => ['nullable', 'string'],
                'value.favicon' => ['nullable', 'string'],
                'value.logo_width' => ['nullable', 'integer', 'min:16', 'max:1000'],
                'value.logo_height' => ['nullable', 'integer', 'min:16', 'max:1000'],
            ],

            'header' => array_merge([
                'value.sticky' => ['boolean'],
                'value.show_search' => ['boolean'],
                'value.show_category_menu' => ['boolean'],
                'value.show_brand_menu' => ['boolean'],
                'value.topbar.enabled' => ['boolean'],
                'value.topbar.text' => ['nullable', 'string', 'max:200'],
                'value.topbar.phone' => ['nullable', 'string', 'max:40'],
                'value.cta.enabled' => ['boolean'],
                'value.cta.label' => ['nullable', 'string', 'max:60'],
                'value.cta.url' => ['nullable', 'string', 'max:255'],
            ], $linkList('value.menu_items')),

            'footer' => [
                'value.about.title' => ['nullable', 'string', 'max:120'],
                'value.about.text' => ['nullable', 'string', 'max:1000'],
                'value.columns' => ['array', 'max:6'],
                'value.columns.*.title' => ['nullable', 'string', 'max:120'],
                'value.columns.*.links' => ['array', 'max:20'],
                'value.columns.*.links.*.label' => ['nullable', 'string', 'max:120'],
                'value.columns.*.links.*.url' => ['nullable', 'string', 'max:255'],
                'value.contact.title' => ['nullable', 'string', 'max:120'],
                'value.contact.address' => ['nullable', 'string', 'max:300'],
                'value.contact.phone' => ['nullable', 'string', 'max:40'],
                'value.contact.mobile' => ['nullable', 'string', 'max:40'],
                'value.contact.email' => ['nullable', 'string', 'email', 'max:120'],
                'value.contact.working_hours' => ['nullable', 'string', 'max:160'],
                'value.copyright' => ['nullable', 'string', 'max:300'],
                'value.show_newsletter' => ['boolean'],
            ],

            'social' => [
                'value.items' => ['array', 'max:12'],
                'value.items.*.platform' => ['required', 'string', 'max:40'],
                'value.items.*.label' => ['nullable', 'string', 'max:60'],
                'value.items.*.url' => ['nullable', 'string', 'max:255'],
            ],

            'hero' => [
                'value.autoplay' => ['boolean'],
                'value.interval' => ['integer', 'min:1500', 'max:30000'],
                'value.slides' => ['array', 'max:12'],
                'value.slides.*.eyebrow' => ['nullable', 'string', 'max:120'],
                'value.slides.*.title' => ['nullable', 'string', 'max:160'],
                'value.slides.*.description' => ['nullable', 'string', 'max:400'],
                'value.slides.*.image' => ['nullable', 'string'],
                'value.slides.*.cta_label' => ['nullable', 'string', 'max:60'],
                'value.slides.*.cta_url' => ['nullable', 'string', 'max:255'],
            ],

            'features' => [
                'value.enabled' => ['boolean'],
                'value.eyebrow' => ['nullable', 'string', 'max:120'],
                'value.title' => ['nullable', 'string', 'max:120'],
                'value.items' => ['array', 'max:12'],
                'value.items.*.icon' => ['nullable', 'string', 'max:40'],
                'value.items.*.title' => ['nullable', 'string', 'max:120'],
                'value.items.*.description' => ['nullable', 'string', 'max:300'],
            ],

            'promo' => [
                'value.enabled' => ['boolean'],
                'value.eyebrow' => ['nullable', 'string', 'max:120'],
                'value.title' => ['nullable', 'string', 'max:120'],
                'value.description' => ['nullable', 'string', 'max:500'],
                'value.badge' => ['nullable', 'string', 'max:40'],
                'value.image' => ['nullable', 'string'],
                'value.cta_label' => ['nullable', 'string', 'max:60'],
                'value.cta_url' => ['nullable', 'string', 'max:255'],
            ],

            'testimonials' => [
                'value.enabled' => ['boolean'],
                'value.eyebrow' => ['nullable', 'string', 'max:120'],
                'value.title' => ['nullable', 'string', 'max:120'],
                'value.items' => ['array', 'max:24'],
                'value.items.*.name' => ['required', 'string', 'max:120'],
                'value.items.*.role' => ['nullable', 'string', 'max:120'],
                'value.items.*.avatar' => ['nullable', 'string'],
                'value.items.*.rating' => ['nullable', 'integer', 'min:1', 'max:5'],
                'value.items.*.text' => ['nullable', 'string', 'max:600'],
            ],

            'articles' => [
                'value.enabled' => ['boolean'],
                'value.eyebrow' => ['nullable', 'string', 'max:120'],
                'value.title' => ['nullable', 'string', 'max:120'],
                'value.items' => ['array', 'max:24'],
                'value.items.*.title' => ['nullable', 'string', 'max:160'],
                'value.items.*.excerpt' => ['nullable', 'string', 'max:400'],
                'value.items.*.image' => ['nullable', 'string'],
                'value.items.*.url' => ['nullable', 'string', 'max:255'],
                'value.items.*.date' => ['nullable', 'string', 'max:40'],
            ],

            'newsletter' => [
                'value.enabled' => ['boolean'],
                'value.eyebrow' => ['nullable', 'string', 'max:120'],
                'value.title' => ['nullable', 'string', 'max:120'],
                'value.description' => ['nullable', 'string', 'max:400'],
                'value.placeholder' => ['nullable', 'string', 'max:80'],
                'value.button_label' => ['nullable', 'string', 'max:60'],
            ],

            'about_page' => [
                'value.title' => ['nullable', 'string', 'max:120'],
                'value.subtitle' => ['nullable', 'string', 'max:200'],
                'value.cover' => ['nullable', 'string'],
                'value.body' => ['nullable', 'string', 'max:20000'],
                'value.stats' => ['array', 'max:8'],
                'value.stats.*.value' => ['nullable', 'string', 'max:40'],
                'value.stats.*.label' => ['nullable', 'string', 'max:120'],
                'value.sections' => ['array', 'max:12'],
                'value.sections.*.title' => ['nullable', 'string', 'max:120'],
                'value.sections.*.text' => ['nullable', 'string', 'max:4000'],
                'value.sections.*.image' => ['nullable', 'string'],
                'value.meta_title' => ['nullable', 'string', 'max:160'],
                'value.meta_description' => ['nullable', 'string', 'max:320'],
            ],

            'contact_page' => [
                'value.title' => ['nullable', 'string', 'max:120'],
                'value.subtitle' => ['nullable', 'string', 'max:200'],
                'value.cover' => ['nullable', 'string'],
                'value.intro' => ['nullable', 'string', 'max:1000'],
                'value.address' => ['nullable', 'string', 'max:300'],
                'value.phones' => ['array', 'max:6'],
                'value.phones.*' => ['nullable', 'string', 'max:40'],
                'value.emails' => ['array', 'max:6'],
                'value.emails.*' => ['nullable', 'string', 'email', 'max:120'],
                'value.working_hours' => ['nullable', 'string', 'max:160'],
                // Raw iframe markup for an embedded map; the frontend takes
                // only its src and never injects the markup into the page.
                'value.map_embed' => ['nullable', 'string', 'max:2000'],
                'value.form_enabled' => ['boolean'],
                'value.meta_title' => ['nullable', 'string', 'max:160'],
                'value.meta_description' => ['nullable', 'string', 'max:320'],
            ],

            'seo' => [
                'value.default_title' => ['nullable', 'string', 'max:120'],
                'value.title_template' => ['nullable', 'string', 'max:160'],
                'value.default_description' => ['nullable', 'string', 'max:320'],
                'value.default_keywords' => ['nullable', 'string', 'max:500'],
                'value.og_image' => ['nullable', 'string'],
                'value.twitter_handle' => ['nullable', 'string', 'max:60'],
                'value.robots_index' => ['boolean'],
                'value.google_site_verification' => ['nullable', 'string', 'max:200'],
                'value.organization.name' => ['nullable', 'string', 'max:160'],
                'value.organization.legal_name' => ['nullable', 'string', 'max:160'],
                'value.organization.logo' => ['nullable', 'string'],
                'value.organization.phone' => ['nullable', 'string', 'max:40'],
                'value.organization.address' => ['nullable', 'string', 'max:300'],
            ],

            'home_sections' => [
                'value.sections' => ['array', 'max:30'],
                'value.sections.*.key' => ['required', 'string', 'max:60'],
                'value.sections.*.enabled' => ['boolean'],
            ],

            'theme' => [
                'value.primary' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'value.accent' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'value.radius' => ['nullable', 'string', 'in:none,sm,md,lg'],
                'value.default_mode' => ['nullable', 'string', 'in:light,dark,system'],
                'value.container' => ['nullable', 'string', 'in:narrow,wide,full'],
            ],
        ];
    }

    /**
     * Rules for one group. Unknown groups get no extra rules; the request
     * itself rejects them via the "key" rule.
     *
     * @return array<string, mixed>
     */
    public static function rulesFor(string $key): array
    {
        return self::rules()[$key] ?? [];
    }
}
