<?php

use App\Http\Middleware\DatabaseHealth;
use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Trusted proxies.
         *
         * The public site is server-side rendered, so during SSR every API
         * call for a page reaches this application from the Nuxt server's
         * single IP address. Without this, the rate limiter would key all
         * visitors to that one address and throttle the site as a whole
         * after a handful of page views.
         *
         * TRUSTED_PROXIES must list the SSR server (and any load balancer or
         * CDN) so X-Forwarded-For is honoured and $request->ip() resolves to
         * the real visitor. It is intentionally empty by default: trusting an
         * unknown proxy would let anyone spoof that header and slip past
         * every per-IP limit in the application.
         */
        $proxies = array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', '')))));

        if ($proxies) {
            $middleware->trustProxies(at: count($proxies) === 1 && $proxies[0] === '*' ? '*' : $proxies);
        }

        $middleware->append(DatabaseHealth::class);
        $middleware->append(SecurityHeaders::class);

        $middleware->prependToGroup('api', ForceJsonResponse::class);

        // Baseline rate limit on the whole "api" group using the "api"
        // limiter defined in AppServiceProvider. Stricter limiters
        // ("admin-write", "auth", "public-read") are layered per route.
        $middleware->throttleApi();

        $middleware->alias([
            'admin' => EnsureIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every API failure answers with the same JSON envelope
        // ({ message, status }) that the frontend's useApi() expects, so the
        // client never has to branch on HTML vs JSON error bodies.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'The submitted data is not valid.',
                    'errors' => $e->errors(),
                    'status' => false,
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'You must sign in to continue.',
                    'status' => false,
                ], 401);
            }

            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'message' => 'You do not have permission to access this area.',
                    'status' => false,
                ], 403);
            }

            if ($e instanceof ThrottleRequestsException) {
                return response()->json([
                    'message' => 'Too many requests. Please try again shortly.',
                    'status' => false,
                ], 429);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'No record matched that request.',
                    'status' => false,
                ], 404);
            }

            return null;
        });
    })->create();
