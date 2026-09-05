<?php

namespace Modules\Seo\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Modules\Brand\Models\Brand;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;
use Modules\Seo\Services\SeoService;

/**
 * Serves sitemap.xml.
 *
 * The document is cached as a whole rather than tied into the per-module
 * cache-version counters: it aggregates three modules, and a short TTL is a
 * simpler contract than invalidating it from three different write paths.
 */
class SitemapController extends Controller
{
    public function __construct(protected SeoService $seo) {}

    protected function ttl(): int
    {
        return (int) config('seo.sitemap_ttl', 3600);
    }

    public function index(): Response
    {
        $xml = Cache::remember('seo:sitemap:xml', $this->ttl(), fn () => $this->buildXml());

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    protected function buildXml(): string
    {
        $urls = $this->staticUrls();

        // chunk() keeps memory flat on a large catalogue: rows are streamed
        // in batches instead of hydrating the whole table at once.
        Product::query()
            ->where('is_active', true)
            ->select('slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->chunk(500, function ($products) use (&$urls) {
                foreach ($products as $product) {
                    $urls[] = [
                        'loc' => $this->seo->url('/products/'.$product->slug),
                        'lastmod' => $product->updated_at?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                    ];
                }
            });

        Category::query()
            ->where('is_active', true)
            ->select('slug', 'updated_at')
            ->chunk(500, function ($categories) use (&$urls) {
                foreach ($categories as $category) {
                    $urls[] = [
                        'loc' => $this->seo->url('/categories/'.$category->slug),
                        'lastmod' => $category->updated_at?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                    ];
                }
            });

        Brand::query()
            ->where('is_active', true)
            ->select('slug', 'updated_at')
            ->chunk(500, function ($brands) use (&$urls) {
                foreach ($brands as $brand) {
                    $urls[] = [
                        'loc' => $this->seo->url('/brands/'.$brand->slug),
                        'lastmod' => $brand->updated_at?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                    ];
                }
            });

        return $this->renderXml($urls);
    }

    /**
     * @return list<array<string, string|null>>
     */
    protected function staticUrls(): array
    {
        return [
            ['loc' => $this->seo->url('/'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $this->seo->url('/products'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $this->seo->url('/categories'), 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $this->seo->url('/brands'), 'changefreq' => 'weekly', 'priority' => '0.7'],
            ['loc' => $this->seo->url('/about'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => $this->seo->url('/contact'), 'changefreq' => 'monthly', 'priority' => '0.5'],
        ];
    }

    /**
     * @param  list<array<string, string|null>>  $urls
     */
    protected function renderXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars((string) $url['loc'], ENT_XML1).'</loc>'."\n";

            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>'.$url['lastmod'].'</lastmod>'."\n";
            }

            $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$url['priority'].'</priority>'."\n";
            $xml .= "  </url>\n";
        }

        return $xml.'</urlset>';
    }
}
