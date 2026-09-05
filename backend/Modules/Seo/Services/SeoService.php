<?php

namespace Modules\Seo\Services;

use Modules\Settings\Services\SettingService;

/**
 * Builds the SEO payload the frontend renders into <head>.
 *
 * Everything here is derived from the settings module, so an admin changing
 * the site name or default description immediately changes the meta tags and
 * the structured data without a deploy.
 */
class SeoService
{
    public function __construct(protected SettingService $settings) {}

    /**
     * Canonical public origin of the site (no trailing slash).
     *
     * SITE_URL wins; otherwise the first entry of FRONTEND_URLS - already
     * maintained for CORS - doubles as the public URL, so there is no second
     * env var carrying the same value.
     */
    public function baseUrl(): string
    {
        $explicit = trim((string) config('app.site_url', ''));

        if ($explicit !== '') {
            return rtrim($explicit, '/');
        }

        $origins = array_values(array_filter(array_map('trim', explode(',', (string) env('FRONTEND_URLS', '')))));

        return rtrim($origins[0] ?? (string) config('app.url'), '/');
    }

    public function url(string $path = '/'): string
    {
        return $this->baseUrl().'/'.ltrim($path, '/');
    }

    /**
     * Site-wide defaults: title template, description, OG image, robots
     * directive and verification token.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        $seo = (array) $this->settings->find('seo');
        $identity = (array) $this->settings->find('identity');

        return [
            'site_name' => $identity['site_name'] ?? ($seo['default_title'] ?? ''),
            'title' => $seo['default_title'] ?? '',
            'title_template' => $seo['title_template'] ?? '%s',
            'description' => $seo['default_description'] ?? '',
            'keywords' => $seo['default_keywords'] ?? '',
            'og_image' => $this->absolute($seo['og_image'] ?? ''),
            'twitter_handle' => $seo['twitter_handle'] ?? '',
            'robots' => ($seo['robots_index'] ?? true) ? 'index, follow' : 'noindex, nofollow',
            'google_site_verification' => $seo['google_site_verification'] ?? '',
            'locale' => 'en_US',
            'base_url' => $this->baseUrl(),
        ];
    }

    /**
     * schema.org Organization node, emitted once on every page.
     *
     * @return array<string, mixed>
     */
    public function organizationJsonLd(): array
    {
        $seo = (array) $this->settings->find('seo');
        $organization = (array) ($seo['organization'] ?? []);
        $social = (array) ($this->settings->value('social.items', []));

        $sameAs = collect($social)
            ->pluck('url')
            ->filter(fn ($url) => is_string($url) && $url !== '')
            ->values()
            ->all();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $organization['name'] ?? null,
            'legalName' => $organization['legal_name'] ?: null,
            'url' => $this->baseUrl(),
            'logo' => $this->absolute($organization['logo'] ?? ''),
            'telephone' => $organization['phone'] ?: null,
            'address' => $organization['address'] ? [
                '@type' => 'PostalAddress',
                'streetAddress' => $organization['address'],
                'addressCountry' => 'IR',
            ] : null,
            'sameAs' => $sameAs ?: null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * schema.org WebSite node with the search action, which is what enables a
     * sitelinks search box in Google results.
     *
     * @return array<string, mixed>
     */
    public function websiteJsonLd(): array
    {
        $defaults = $this->defaults();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $defaults['site_name'],
            'url' => $this->baseUrl(),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $this->url('/products').'?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * Turns a stored path into an absolute URL, leaving already-absolute
     * values untouched.
     */
    public function absolute(?string $path): string
    {
        if (empty($path)) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return $this->url($path);
    }
}
