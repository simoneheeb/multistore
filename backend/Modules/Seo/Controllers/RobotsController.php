<?php

namespace Modules\Seo\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\Seo\Services\SeoService;
use Modules\Settings\Services\SettingService;

/**
 * Serves robots.txt, generated from the SEO settings so an admin can take the
 * whole site out of the index without touching a file on the server.
 */
class RobotsController extends Controller
{
    public function __construct(
        protected SeoService $seo,
        protected SettingService $settings,
    ) {}

    public function index(): Response
    {
        $indexable = (bool) $this->settings->value('seo.robots_index', true);

        $lines = ['User-agent: *'];

        if ($indexable) {
            // Admin surfaces and the API itself carry no content worth
            // indexing and would only waste crawl budget.
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /auth';
            $lines[] = 'Disallow: /api';
            $lines[] = 'Allow: /';
            $lines[] = '';
            $lines[] = 'Sitemap: '.$this->seo->url('/api/sitemap.xml');
        } else {
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
