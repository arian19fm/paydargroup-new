<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Media;
use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        // Only real counts; no analytics.
        $counts = [];
        $recent = [];

        if ($user->can('pages.view')) {
            $counts['pages'] = ['total' => Page::count(), 'published' => Page::published()->count(), 'route' => 'admin.pages.index', 'icon' => 'pages'];
            $recent['pages'] = Page::query()->latest('updated_at')->limit(5)->get(['id', 'title', 'status', 'published_at', 'updated_at']);
        }

        if ($user->can('articles.view')) {
            $counts['articles'] = ['total' => Article::count(), 'published' => Article::published()->count(), 'route' => 'admin.articles.index', 'icon' => 'articles'];
            $recent['articles'] = Article::query()->latest('updated_at')->limit(5)->get(['id', 'title', 'status', 'published_at', 'updated_at']);
        }

        if ($user->can('media.view')) {
            $counts['media'] = ['total' => Media::count(), 'route' => 'admin.media.index', 'icon' => 'media'];
        }

        if ($user->can('redirects.view')) {
            $counts['redirects'] = ['total' => Redirect::count(), 'active' => Redirect::active()->count(), 'route' => 'admin.redirects.index', 'icon' => 'redirects'];
        }

        $quick = array_values(array_filter([
            $user->can('articles.create') ? ['route' => 'admin.articles.create', 'icon' => 'articles', 'label' => __('admin.quick.new_article')] : null,
            $user->can('media.manage') ? ['route' => 'admin.media.create', 'icon' => 'media', 'label' => __('admin.quick.upload_media')] : null,
            $user->can('settings.view') ? ['route' => 'admin.settings.edit', 'params' => 'home', 'icon' => 'settings', 'label' => __('admin.quick.home_settings')] : null,
        ]));

        return view('admin.dashboard', compact('counts', 'recent', 'quick'));
    }
}
