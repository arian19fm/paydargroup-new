<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Pages\AboutPage;
use Illuminate\Contracts\View\View;

/**
 * Public managed page by slug. Only published() content resolves; drafts
 * and scheduled pages are a 404 to visitors. The page's template picks the
 * Blade view (config/cms.php → page_templates).
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

        $templates = config('cms.page_templates');
        $template = $page->template && isset($templates[$page->template]) ? $page->template : 'default';

        $data = compact('page');

        if ($template === 'about') {
            $data['about'] = app(AboutPage::class)->data();
        }

        return view($templates[$template], $data);
    }
}
