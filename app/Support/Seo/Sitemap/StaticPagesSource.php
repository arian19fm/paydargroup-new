<?php

namespace App\Support\Seo\Sitemap;

use App\Support\Seo\SeoManager;
use Illuminate\Support\Facades\Route;

/**
 * Fixed public pages defined by named routes. Add a route name here when a
 * new static page is created; managed content gets its own source.
 */
class StaticPagesSource implements SitemapSource
{
    /** @var list<string> */
    protected array $routes = [
        'home',
    ];

    public function urls(): iterable
    {
        foreach ($this->routes as $name) {
            if (Route::has($name)) {
                yield new SitemapUrl(SeoManager::absoluteUrl(parse_url(route($name), PHP_URL_PATH) ?? ''));
            }
        }
    }
}
