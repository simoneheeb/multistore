<?php

namespace Modules\Seo\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Seo\Services\SeoService;

/**
 * Exposes the site-wide SEO defaults and structured data to the frontend, so
 * the Nuxt app can render <head> from server-owned values instead of
 * hard-coding them.
 */
class SeoController extends Controller
{
    public function __construct(protected SeoService $seo) {}

    public function index(Request $request): JsonResponse
    {
        $payload = [
            'defaults' => $this->seo->defaults(),
            'jsonld' => [
                'organization' => $this->seo->organizationJsonLd(),
                'website' => $this->seo->websiteJsonLd(),
            ],
        ];

        $etag = '"'.md5((string) json_encode($payload)).'"';

        if (trim((string) $request->headers->get('If-None-Match')) === $etag) {
            return response()->json(null, 304)->setEtag($etag, true);
        }

        return response()
            ->json($payload + ['status' => true])
            ->setEtag($etag, true)
            ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=3600');
    }
}
