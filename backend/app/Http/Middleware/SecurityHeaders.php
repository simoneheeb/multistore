<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds a baseline set of hardening headers to every response.
 *
 * This API is consumed by a separate Nuxt frontend, so a restrictive CSP is
 * safe here: the API itself never serves HTML that needs to load scripts.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // Stop browsers from second-guessing the declared content type.
            'X-Content-Type-Options' => 'nosniff',
            // API responses must never be framed by another site.
            'X-Frame-Options' => 'DENY',
            // Do not leak full API URLs (which can contain slugs/ids) to third parties.
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            // The API needs none of these device capabilities.
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',
            // Nothing served from this origin is meant to be executed as a page.
            'Content-Security-Policy' => "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none'",
        ];

        foreach ($headers as $key => $value) {
            if (! $response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        // Only advertise HSTS over a real TLS connection, otherwise local
        // http:// development would get pinned to https by the browser.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
