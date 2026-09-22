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

        return view('site.home', [
            'products' => $home->products($pageUrls),
            'articles' => $home->latestArticles(),
            'links' => $links,
            'faqItems' => __('home.faq.items'),
        ]);
    }
}
