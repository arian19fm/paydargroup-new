<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Business;
use App\Models\JobOpening;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SeoMeta;
use App\Models\TeamMember;
use App\Policies\RolePolicy;
use App\Support\Menus\MenuRepository;
use App\Support\Seo\SeoManager;
use App\Support\Seo\Sitemap\Sitemap;
use App\Support\Settings\Settings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
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

        // The sitemap reflects publication, SEO (noindex) and listing
        // changes promptly; its lastmod hints follow jobs and the team too.
        foreach ([Page::class, Article::class, ArticleCategory::class, Business::class, SeoMeta::class, JobOpening::class, TeamMember::class] as $model) {
            $model::saved(fn () => Sitemap::flush());
            $model::deleted(fn () => Sitemap::flush());
        }
    }
}
