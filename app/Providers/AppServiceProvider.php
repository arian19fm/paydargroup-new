<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Support\Menus\MenuRepository;
use App\Support\Seo\SeoManager;
use App\Support\Settings\Settings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->app->singleton(Settings::class);
        $this->app->singleton(MenuRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pagination markup matches the Bootstrap-based UI.
        Paginator::useBootstrapFive();

        // Public contact form: a handful of submissions per minute per IP is
        // plenty for humans and blunts naive spam without any external service.
        RateLimiter::for('contact-form', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        // Navigation caches depend on menus and on page publication state.
        $flushMenus = fn () => app(MenuRepository::class)->flush();
        Menu::saved($flushMenus);
        Menu::deleted($flushMenus);
        MenuItem::saved($flushMenus);
        MenuItem::deleted($flushMenus);
        Page::saved($flushMenus);
        Page::deleted($flushMenus);

        // Sitemap reflects publication changes promptly.
        $flushSitemap = fn () => Cache::forget(config('seo.sitemap_cache_key', 'seo.sitemap.xml'));
        Page::saved($flushSitemap);
        Page::deleted($flushSitemap);
        Article::saved($flushSitemap);
        Article::deleted($flushSitemap);
    }
}
