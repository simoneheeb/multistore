<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces the JSON branch of Laravel's error handling for API traffic.
 *
 * Without this, a client that forgets the "Accept: application/json" header
 * gets an HTML error page (or a redirect to a non-existent login route) on
 * validation and auth failures, which the frontend cannot parse.
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
