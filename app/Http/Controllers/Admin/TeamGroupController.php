<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamGroupRequest;
use App\Models\TeamGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TeamGroupController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(TeamGroup::class, 'group');
    }

    public function index(): View
    {
        $groups = TeamGroup::query()->withCount('members')->ordered()->paginate(config('cms.per_page'));

        return view('admin.team.groups.index', compact('groups'));
    }

    public function create(): View
    {
        return view('admin.team.groups.form', ['group' => new TeamGroup(['is_active' => true])]);
    }

    public function store(TeamGroupRequest $request): RedirectResponse
    {
        TeamGroup::create($request->groupData());

        return redirect()->route('admin.team.groups.index')->with('success', __('admin.saved'));
    }

    public function edit(TeamGroup $group): View
    {
        return view('admin.team.groups.form', compact('group'));
    }

    public function update(TeamGroupRequest $request, TeamGroup $group): RedirectResponse
    {
        $group->update($request->groupData());

        return redirect()->route('admin.team.groups.index')->with('success', __('admin.saved'));
    }

    /** Members of the group are removed with it (database cascade). */
    public function destroy(TeamGroup $group): RedirectResponse
    {
        $group->delete();

        return redirect()->route('admin.team.groups.index')->with('success', __('admin.deleted'));
    }
}
