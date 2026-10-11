<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsSettingsRequest;
use App\Models\ArticleCategory;
use App\Models\NewsRun;
use App\Models\User;
use App\Support\News\NewsRunner;
use App\Support\News\NewsSettings;
use App\Support\Settings\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Automatic news (App\Support\News): on/off and schedule, what to write,
 * a "run now" button (picked up by the next scheduler tick) and the run log.
 */
class NewsController extends Controller
{
    public function edit(): View
    {
        $this->authorize('viewAny', NewsRun::class);

        $lastTick = cache(NewsRunner::TICK_CACHE_KEY);

        return view('admin.news.edit', [
            'settings' => NewsSettings::load(),
            'stored' => app(Settings::class)->group('news'),
            'runs' => NewsRun::query()->with(['articles:id,news_run_id,title,slug,status', 'requester:id,name'])->latest('id')->paginate(15),
            'active' => NewsRun::query()->active()->exists(),
            'hasApiKey' => NewsSettings::hasApiKey(),
            'schedulerOk' => $lastTick && Carbon::parse($lastTick)->gt(now()->subMinutes(5)),
            'categories' => ArticleCategory::query()->orderBy('name')->pluck('name', 'id')->all(),
            'authors' => User::query()->orderBy('name')->pluck('name', 'id')->all(),
        ]);
    }

    public function update(NewsSettingsRequest $request): RedirectResponse
    {
        app(Settings::class)->setGroup('news', $request->values());

        return redirect()->route('admin.news.edit')->with('success', __('admin.saved'));
    }

    public function run(Request $request): RedirectResponse
    {
        $this->authorize('create', NewsRun::class);

        if (! NewsSettings::hasApiKey()) {
            return back()->with('error', __('admin.news.no_api_key'));
        }

        if (NewsRun::query()->active()->exists()) {
            return back()->with('error', __('admin.news.already_running'));
        }

        NewsRun::create(['trigger' => 'manual', 'status' => NewsRun::QUEUED, 'requested_by' => $request->user()->id]);

        return back()->with('success', __('admin.news.queued'));
    }
}
