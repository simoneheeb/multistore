<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Rate Limits
    |--------------------------------------------------------------------------
    |
    | Requests allowed per minute for each named limiter. The limiters
    | themselves live in App\Providers\AppServiceProvider and are attached
    | to routes through the "throttle:<name>" middleware.
    |
    | Every value is env-driven so a deployment can be tuned - a marketing
    | campaign may need a higher public ceiling, a staging box a lower one -
    | without touching code. The defaults suit a normal storefront.
    |
    */

    // Global ceiling for the whole API surface. Deliberately generous: it
    // exists to stop runaway clients, not to shape normal traffic.
    'api' => (int) env('RATE_LIMIT_API', 120),

    // Public catalogue browsing (products, categories, brands, settings).
    // These responses are cached server-side, so a high ceiling is cheap,
    // but it still caps scraping to a sane rate per visitor.
    'public_read' => (int) env('RATE_LIMIT_PUBLIC_READ', 90),

    // Admin writes (create/update/delete). Low volume, high impact, so a
    // tight limit both protects the data and slows abuse of a leaked token.
    'admin_write' => (int) env('RATE_LIMIT_ADMIN_WRITE', 30),

    // File uploads are the most expensive write path (disk + bandwidth), so
    // they get their own, even tighter bucket.
    'uploads' => (int) env('RATE_LIMIT_UPLOADS', 20),

    // Login attempts. Kept strict to slow credential stuffing.
    'auth' => (int) env('RATE_LIMIT_AUTH', 5),

];
