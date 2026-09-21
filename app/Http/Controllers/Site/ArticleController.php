<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\Seo\JsonLd;
use Illuminate\Contracts\View\View;

/**
 * Public article by slug. Article JSON-LD is added here and nowhere else.
 */
class ArticleController extends Controller
{
    public function show(string $slug): View
    {
        $article = Article::query()->published()->where('slug', $slug)
            ->with(['author:id,name', 'categories', 'featuredImage', 'seo.ogImage', 'seo.twitterImage'])
            ->firstOrFail();

        seo()->fromModel($article)
            ->breadcrumbs([
                ['label' => __('nav.home'), 'url' => route('home')],
                ['label' => __('nav.articles')],
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

        return view('site.articles.show', compact('article'));
    }
}
