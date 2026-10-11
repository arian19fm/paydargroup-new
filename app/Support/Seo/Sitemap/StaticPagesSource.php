<?php

namespace App\Support\Seo\Sitemap;

use App\Models\Article;
use App\Models\Business;
use App\Models\JobOpening;
use App\Models\TeamMember;
use App\Support\Seo\SeoManager;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/**
 * Fixed public pages defined by named routes. Add a route name here when a
 * new static page is created; managed content gets its own source. Pages
 * that list content take their lastmod from the newest item they show.
 */
class StaticPagesSource implements SitemapSource
{
    /** @var list<string> */
    protected array $routes = [
        'home',
        'articles.index',
        'careers',
        'team',
        'contact',
    ];

    public function urls(): iterable
    {
        foreach ($this->routes as $name) {
            if (Route::has($name)) {
                yield new SitemapUrl(SeoManager::absoluteUrl(parse_url(route($name), PHP_URL_PATH) ?? ''), $this->lastModified($name));
            }
        }
    }

    protected function lastModified(string $route): ?CarbonInterface
    {
        $latestArticle = fn () => $this->max(Article::query()->published()->max('updated_at'));

        return match ($route) {
            'home' => collect([$latestArticle(), $this->max(Business::query()->published()->max('updated_at'))])->filter()->max(),
            'articles.index' => $latestArticle(),
            'careers' => $this->max(JobOpening::query()->active()->max('updated_at')),
            'team' => $this->max(TeamMember::query()->active()->max('updated_at')),
            default => null,
        };
    }

    protected function max(mixed $value): ?CarbonInterface
    {
        return $value ? Carbon::parse($value) : null;
    }
}
