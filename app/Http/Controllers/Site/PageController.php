<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\View\View;

/**
 * Public managed page by slug. Only published() content resolves; drafts
 * and scheduled pages are a 404 to visitors.
 */
class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()->published()->where('slug', $slug)->with('seo.ogImage', 'seo.twitterImage')->firstOrFail();

        seo()->fromModel($page)->breadcrumbs([
            ['label' => __('nav.home'), 'url' => route('home')],
            ['label' => $page->title],
        ]);

        return view('site.pages.show', compact('page'));
    }
}
