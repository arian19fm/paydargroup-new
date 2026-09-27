<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\TeamGroup;
use Illuminate\Contracts\View\View;

/**
 * Team page (Figma 229:12 / 246:675): active groups in order, each with
 * its active members. Groups without visible members are skipped.
 */
class TeamPageController extends Controller
{
    public function __invoke(): View
    {
        seo()->title(__('team.title'))
            ->description(__('team.description'))
            ->breadcrumbs([
                ['label' => __('nav.home'), 'url' => route('home')],
                ['label' => __('team.title')],
            ]);

        $groups = TeamGroup::query()->active()->ordered()
            ->with(['members' => fn ($q) => $q->active()->with('photo')])
            ->get()
            ->filter(fn (TeamGroup $group) => $group->members->isNotEmpty())
            ->values();

        return view('site.team', compact('groups'));
    }
}
