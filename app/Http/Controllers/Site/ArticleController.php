<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\Content\ArticleBody;
use App\Support\Seo\JsonLd;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Blog: the listing (Figma 276:13365 / 302:8 — category chips, 3-column
 * grid, numbered pager) and the single article (313:13 / 315:1483 — hero
 * image, body with a sidebar of date, table of contents and share links,
 * then the latest posts and the contact card). Article JSON-LD is added
 * here and nowhere else.
 */
class ArticleController extends Controller
{
    public const PER_PAGE = 9;

    public function index(Request $request): View
    {
        $categories = ArticleCategory::query()->active()->ordered()->get(['id', 'name', 'slug']);
        $current = null;

        if ($slug = $request->string('category')->toString()) {
            $current = $categories->firstWhere('slug', $slug);
            abort_unless($current, 404);
        }

        $articles = Article::query()->published()
            ->with(['featuredImage', 'categories' => fn ($q) => $q->active()->ordered()])
            ->when($current, fn ($q) => $q->whereHas('categories', fn ($c) => $c->whereKey($current->id)))
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        seo()->title($current ? $current->name.' | '.__('articles.title') : __('articles.title'))
            ->description(__('articles.description'))
            ->canonicalQuery(['category'])
            ->breadcrumbs(array_filter([
                ['label' => __('nav.home'), 'url' => route('home')],
                ['label' => __('articles.title'), 'url' => $current ? route('articles.index') : null],
                $current ? ['label' => $current->name] : null,
            ]));

        return view('site.articles.index', compact('articles', 'categories', 'current'));
    }

    public function show(string $slug): View
    {
        $article = Article::query()->published()->where('slug', $slug)
            ->with(['author:id,name', 'categories' => fn ($q) => $q->active()->ordered(), 'featuredImage', 'seo.ogImage', 'seo.twitterImage'])
            ->firstOrFail();

        seo()->fromModel($article)
            ->breadcrumbs([
                ['label' => __('nav.home'), 'url' => route('home')],
                ['label' => __('articles.title'), 'url' => route('articles.index')],
                ['label' => $article->title],
            ])
            ->jsonLd(JsonLd::article([
                'type' => $article->seoMeta()?->schema_type ?: 'Article',
                'headline' => $article->title,
                'description' => $article->seoMeta()?->description ?: $article->excerpt,
                'url' => $article->publicUrl(),
                'image' => $article->featuredImage?->url(),
                'datePublished' => $article->published_at,
                'dateModified' => $article->updated_at,
                'author' => $article->author?->name,
            ]));

        $body = ArticleBody::render($article->content);

        $latest = Article::query()->published()
            ->whereKeyNot($article->id)
            ->with(['featuredImage', 'categories' => fn ($q) => $q->active()->ordered()])
            ->orderByDesc('published_at')
            ->limit((int) config('home.articles_limit', 4))
            ->get();

        $url = $article->publicUrl();
        $share = [
            'x' => 'https://twitter.com/intent/tweet?'.http_build_query(['url' => $url, 'text' => $article->title]),
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?'.http_build_query(['u' => $url]),
            'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?'.http_build_query(['url' => $url]),
        ];

        return view('site.articles.show', [
            'article' => $article,
            'body' => $body['html'],
            'toc' => $body['toc'],
            'latest' => $latest,
            'share' => $share,
            'links' => ['blog' => route('articles.index')],
        ]);
    }
}
