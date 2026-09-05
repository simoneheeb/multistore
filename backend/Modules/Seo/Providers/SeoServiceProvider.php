<?php

namespace Modules\Seo\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class SeoServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Seo';

    protected string $nameLower = 'seo';

    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
