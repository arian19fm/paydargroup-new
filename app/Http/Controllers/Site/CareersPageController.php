<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use App\Support\Careers\CareersPage;
use Illuminate\Contracts\View\View;

/**
 * Careers page (Figma 339:352 desktop / 348:79 mobile): intro + photo,
 * the benefits of working at the group, and the active job openings.
 */
class CareersPageController extends Controller
{
    public function __invoke(CareersPage $careers): View
    {
        seo()->title(__('careers.title'))
            ->description(__('careers.description'))
            ->breadcrumbs([
                ['label' => __('nav.home'), 'url' => route('home')],
                ['label' => __('careers.title')],
            ]);

        return view('site.careers', [
            'copy' => $careers->copy(),
            'benefits' => $careers->benefits(),
            'heroImage' => $careers->heroImage(),
            'jobs' => $careers->jobs(),
        ]);
    }

    /**
     * One opening on its own page: the detail block and the application
     * form the modal shows, for deep links and visitors without JavaScript.
     */
    public function show(JobOpening $job, CareersPage $careers): View
    {
        abort_unless($job->is_active, 404);

        seo()->title($job->title.' | '.__('careers.title'))
            ->description($job->description ?: __('careers.description'))
            ->breadcrumbs([
                ['label' => __('nav.home'), 'url' => route('home')],
                ['label' => __('careers.title'), 'url' => route('careers')],
                ['label' => $job->title],
            ]);

        return view('site.careers-job', ['job' => $job, 'copy' => $careers->copy()]);
    }
}
