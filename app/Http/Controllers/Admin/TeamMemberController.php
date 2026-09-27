<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamMemberRequest;
use App\Models\TeamGroup;
use App\Models\TeamMember;
use App\Support\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function __construct(protected MediaService $media)
    {
        $this->authorizeResource(TeamMember::class, 'member');
    }

    public function index(Request $request): View
    {
        $members = TeamMember::query()
            ->with(['group:id,name', 'photo:id,disk,path,original_filename'])
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('team_members.name', 'like', "%{$term}%")
                ->orWhere('team_members.role', 'like', "%{$term}%")))
            ->join('team_groups', 'team_groups.id', '=', 'team_members.team_group_id')
            ->orderBy('team_groups.sort_order')->orderBy('team_members.sort_order')->orderBy('team_members.id')
            ->select('team_members.*')
            ->paginate(config('cms.per_page'))
            ->withQueryString();

        return view('admin.team.members.index', compact('members'));
    }

    public function create(): View
    {
        return view('admin.team.members.form', ['member' => new TeamMember(['is_active' => true]), 'groups' => $this->groups()]);
    }

    public function store(TeamMemberRequest $request): RedirectResponse
    {
        TeamMember::create($this->withUploadedPhoto($request, $request->memberData()));

        return redirect()->route('admin.team.members.index')->with('success', __('admin.saved'));
    }

    public function edit(TeamMember $member): View
    {
        return view('admin.team.members.form', ['member' => $member, 'groups' => $this->groups()]);
    }

    public function update(TeamMemberRequest $request, TeamMember $member): RedirectResponse
    {
        $member->update($this->withUploadedPhoto($request, $request->memberData()));

        return redirect()->route('admin.team.members.index')->with('success', __('admin.saved'));
    }

    public function destroy(TeamMember $member): RedirectResponse
    {
        $member->delete();

        return redirect()->route('admin.team.members.index')->with('success', __('admin.deleted'));
    }

    /** A photo uploaded from the member form goes into the media library, named after the member. */
    protected function withUploadedPhoto(TeamMemberRequest $request, array $data): array
    {
        if ($request->hasFile('photo')) {
            $data['photo_media_id'] = $this->media->upload($request->file('photo'), $request->user(), [
                'title' => $data['name'],
                'alt_text' => $data['name'],
            ])->id;
        }

        return $data;
    }

    protected function groups(): array
    {
        return TeamGroup::query()->ordered()->pluck('name', 'id')->all();
    }
}
