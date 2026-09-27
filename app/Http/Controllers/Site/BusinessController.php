<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Contracts\View\View;

/**
 * Public business page by slug (Figma 205:119 desktop / 220:5 mobile):
 * hero, intro and benefits. Only published() businesses resolve; drafts
 * and scheduled ones are a 404.
 */
class BusinessController extends Controller
{
    public function show(string $slug): View
    {
        $business = Business::query()->published()->where('slug', $slug)
            ->with(['image', 'benefitsMedia', 'seo.ogImage', 'seo.twitterImage'])
            ->firstOrFail();

        seo()->fromModel($business)->breadcrumbs([
            ['label' => __('nav.home'), 'url' => route('home')],
            ['label' => __('businesses.title'), 'url' => route('home').'#products'],
            ['label' => $business->title],
        ]);

        return view('site.businesses.show', compact('business'));
    }
}
