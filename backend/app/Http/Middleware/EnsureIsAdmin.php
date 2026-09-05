<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorization gate for every /api/admin/* endpoint.
 *
 * "auth:sanctum" only proves the caller holds a valid token; it says nothing
 * about what that token is allowed to do. Without this middleware any
 * registered account could create products or overwrite site settings, so the
 * admin flag is checked explicitly here.
 */
class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            return response()->json([
                'message' => 'This area is not available to your account.',
                'status' => false,
            ], 401);
        }

        if (! $user->is_admin) {
            return response()->json([
                'message' => 'You do not have permission to access this area.',
                'status' => false,
            ], 403);
        }

        return $next($request);
    }
}
