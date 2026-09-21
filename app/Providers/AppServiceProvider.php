<?php

namespace App\Providers;

use App\Support\Seo\SeoManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One SEO state per request (scoped, so long-running workers such as
        // Octane never leak metadata between requests).
        $this->app->scoped(SeoManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
