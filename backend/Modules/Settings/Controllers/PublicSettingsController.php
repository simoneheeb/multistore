<?php

namespace Modules\Settings\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Settings\Services\SettingService;

/**
 * Read-only settings endpoint consumed by the public site on every render.
 *
 * The payload is served from cache and additionally carries an ETag, so a
 * warm browser or CDN revalidates with a 304 instead of re-downloading the
 * whole settings tree.
 */
class PublicSettingsController extends Controller
{
    public function __construct(protected SettingService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->all();

        $etag = '"'.md5((string) json_encode($data)).'"';

        if (trim((string) $request->headers->get('If-None-Match')) === $etag) {
            return response()->json(null, 304)->setEtag($etag, true);
        }

        return response()
            ->json(['data' => $data, 'status' => true])
            ->setEtag($etag, true)
            ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=600');
    }
}
