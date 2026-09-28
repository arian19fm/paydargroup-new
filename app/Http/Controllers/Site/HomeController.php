<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Home\HomePage;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Render the public home page. No page title is set, so <x-seo.head>
     * renders the site name (plus tagline when configured); the description
     * falls back to the site default — see docs/SEO.md.
     */
    public function __invoke(HomePage $home): View
    {
        $pageUrls = $home->pageUrls($home->linkedSlugs());
        $links = collect(config('home.links', []))->map(fn (string $slug) => $pageUrls[$slug] ?? null)->all();
        // The blog has its own listing; a CMS page with the `blog` slug may still override it.
        $links['blog'] ??= route('articles.index');
        [$heroVideo, $heroImage] = $home->heroMedia();

        return view('site.home', [
            'heroVideo' => $heroVideo,
            'heroImage' => $heroImage,
            'products' => $home->products($pageUrls),
            'productsSection' => $home->productsSection($links),
            'articles' => $home->latestArticles(),
            'links' => $links,
            'faqItems' => $home->faqItems(),
        ]);
    }
}
