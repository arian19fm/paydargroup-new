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

        if ($user->can('pages.view')) {
            $counts['pages'] = ['total' => Page::count(), 'published' => Page::published()->count()];
        }

        if ($user->can('articles.view')) {
            $counts['articles'] = ['total' => Article::count(), 'published' => Article::published()->count()];
        }

        if ($user->can('media.view')) {
            $counts['media'] = ['total' => Media::count()];
        }

        if ($user->can('redirects.view')) {
            $counts['redirects'] = ['total' => Redirect::count(), 'active' => Redirect::active()->count()];
        }

        return view('admin.dashboard', compact('counts'));
    }
}
