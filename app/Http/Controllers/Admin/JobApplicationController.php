<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Inbox of résumés sent from the careers page. Opening one marks it seen,
 * which lowers the sidebar counter; the file is streamed from the private
 * disk only to authorised staff.
 */
class JobApplicationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(JobApplication::class, 'application', ['except' => ['resume']]);
    }

    public function index(Request $request): View
    {
        $applications = JobApplication::query()
            ->with('job:id,title')
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('phone', 'like', "%{$term}%")
                ->orWhere('job_title', 'like', "%{$term}%")))
            ->when($request->string('status')->toString() === 'unseen', fn ($q) => $q->unseen())
            ->latest('id')
            ->paginate(config('cms.per_page'))
            ->withQueryString();

        return view('admin.applications.index', compact('applications'));
    }

    public function show(JobApplication $application): View
    {
        $application->markSeen();

        return view('admin.applications.show', compact('application'));
    }

    public function resume(JobApplication $application): StreamedResponse
    {
        $this->authorize('view', $application);

        abort_unless(Storage::disk(JobApplication::DISK)->exists($application->resume_path), 404);

        $application->markSeen();

        return Storage::disk(JobApplication::DISK)->download($application->resume_path, $application->resume_name);
    }

    public function destroy(JobApplication $application): RedirectResponse
    {
        $application->delete();

        return redirect()->route('admin.applications.index')->with('success', __('admin.deleted'));
    }
}
