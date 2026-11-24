<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Contracts\View\View;

/**
 * App download page (Figma 558:766 desktop / 578:1074 mobile): the
 * published businesses whose app is switched on in the admin, each with
 * its Android file, the iPhone "add to home screen" guide, QR code and website.
 */
class AppsPageController extends Controller
{
    public function __invoke(): View
    {
        seo()->title(__('apps.title'))
            ->description(__('apps.description'))
            ->breadcrumbs([
                ['label' => __('nav.home'), 'url' => route('home')],
                ['label' => __('apps.title')],
            ]);

        $apps = Business::query()->published()->withApp()->ordered()
            ->with(['appLogo', 'appQr'])
            ->get();

        return view('site.apps', compact('apps'));
    }
}
