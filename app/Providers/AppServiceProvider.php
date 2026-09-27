<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Business;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Policies\RolePolicy;
use App\Support\Menus\MenuRepository;
use App\Support\Seo\SeoManager;
use App\Support\Settings\Settings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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

        // The Role model lives in the package, so it is outside policy auto-discovery.
        Gate::policy(Role::class, RolePolicy::class);

        // Public contact form: a handful of submissions per minute per IP is
        // plenty for humans and blunts naive spam without any external service.
        RateLimiter::for('contact-form', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('job-apply', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

        // Navigation caches depend on menus, on page publication state and on
        // the businesses (they are listed automatically under the
        // "businesses" menu item).
        $flushMenus = fn () => app(MenuRepository::class)->flush();
        Menu::saved($flushMenus);
        Menu::deleted($flushMenus);
        MenuItem::saved($flushMenus);
        MenuItem::deleted($flushMenus);
        Page::saved($flushMenus);
        Page::deleted($flushMenus);
        Business::saved($flushMenus);
        Business::deleted($flushMenus);

        // Sitemap reflects publication changes promptly.
        $flushSitemap = fn () => Cache::forget(config('seo.sitemap_cache_key', 'seo.sitemap.xml'));
        Page::saved($flushSitemap);
        Page::deleted($flushSitemap);
        Article::saved($flushSitemap);
        Article::deleted($flushSitemap);
        Business::saved($flushSitemap);
        Business::deleted($flushSitemap);
    }
}
