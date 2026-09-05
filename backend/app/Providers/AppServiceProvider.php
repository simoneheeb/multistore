<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiting();

        // Behind a reverse proxy that terminates TLS, generated URLs
        // (sitemap entries, storage links) must still be https.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Central definition of every API rate limit.
     *
     * Applied through the "throttle:<name>" middleware: the "api" limiter is
     * attached to the whole API group in bootstrap/app.php, the others are
     * attached per route group in each module's routes/api.php.
     *
     * The per-minute allowances come from config/rate_limits.php so they can
     * be tuned per environment; only the keying strategy lives here.
     *
     * All limiters return a JSON body so a blocked request is still readable
     * by the frontend instead of surfacing as an unparsable error.
     */
    protected function configureRateLimiting(): void
    {
        // Global ceiling for the entire API surface. Deliberately generous:
        // it exists to stop runaway clients, not to shape normal traffic.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute($this->limit('api', 120))
                ->by($request->user()?->id ?: $request->ip())
                ->response(fn () => $this->tooManyRequests());
        });

        // Public catalogue browsing (products, categories, brands, settings).
        // These responses are cached server-side, so a high ceiling is cheap,
        // but it still caps scraping to a sane rate per visitor.
        RateLimiter::for('public-read', function (Request $request) {
            return Limit::perMinute($this->limit('public_read', 90))
                ->by('read:'.($request->user()?->id ?: $request->ip()))
                ->response(fn () => $this->tooManyRequests());
        });

        // Admin writes (create/update/delete). Low volume, high impact, so a
        // tight limit both protects the data and slows down abuse of a
        // leaked token.
        RateLimiter::for('admin-write', function (Request $request) {
            return Limit::perMinute($this->limit('admin_write', 30))
                ->by('admin:'.($request->user()?->id ?: $request->ip()))
                ->response(fn () => $this->tooManyRequests());
        });

        // File uploads are the most expensive write path (disk + bandwidth),
        // so they get their own, even tighter bucket.
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute($this->limit('uploads', 20))
                ->by('upload:'.($request->user()?->id ?: $request->ip()))
                ->response(fn () => $this->tooManyRequests());
        });

        // Login attempts: strict per-IP limit to slow credential stuffing.
        // Keyed by IP *and* by the submitted email so one attacker cannot
        // lock every account out by hammering a single address.
        RateLimiter::for('auth', function (Request $request) {
            $perMinute = $this->limit('auth', 5);

            return [
                Limit::perMinute($perMinute)->by('auth-ip:'.$request->ip())
                    ->response(fn () => $this->tooManyRequests('Too many sign-in attempts. Please try again in a few minutes.')),
                Limit::perMinute($perMinute)->by('auth-user:'.mb_strtolower((string) $request->input('email')))
                    ->response(fn () => $this->tooManyRequests('Too many sign-in attempts. Please try again in a few minutes.')),
            ];
        });
    }

    /**
     * One configured allowance, clamped to at least 1.
     *
     * A misconfigured 0 or a negative value would otherwise block every
     * request outright, which is a far worse failure than a loose limit.
     */
    protected function limit(string $name, int $fallback): int
    {
        return max(1, (int) config("rate_limits.{$name}", $fallback));
    }

    /**
     * Shared 429 body so every limiter answers in the same shape.
     */
    protected function tooManyRequests(?string $message = null)
    {
        return response()->json([
            'message' => $message ?: 'Too many requests. Please try again shortly.',
            'status' => false,
        ], 429);
    }
}
