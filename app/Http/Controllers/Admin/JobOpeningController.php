<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JobOpeningRequest;
use App\Models\JobOpening;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobOpeningController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(JobOpening::class, 'job');
    }

    public function index(Request $request): View
    {
        $jobs = JobOpening::query()
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%")))
            ->ordered()
            ->paginate(config('cms.per_page'))
            ->withQueryString();

        return view('admin.jobs.index', compact('jobs'));
    }

    public function create(): View
    {
        return view('admin.jobs.form', ['job' => new JobOpening(['is_active' => true, 'tone' => 'blue'])]);
    }

    public function store(JobOpeningRequest $request): RedirectResponse
    {
        JobOpening::create($request->jobData());

        return redirect()->route('admin.jobs.index')->with('success', __('admin.saved'));
    }

    public function edit(JobOpening $job): View
    {
        return view('admin.jobs.form', compact('job'));
    }

    public function update(JobOpeningRequest $request, JobOpening $job): RedirectResponse
    {
        $job->update($request->jobData());

        return redirect()->route('admin.jobs.index')->with('success', __('admin.saved'));
    }

    public function destroy(JobOpening $job): RedirectResponse
    {
        $job->delete();

        return redirect()->route('admin.jobs.index')->with('success', __('admin.deleted'));
    }
}
