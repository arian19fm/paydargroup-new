<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreJobApplicationRequest;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * Receives the careers application form (modal or the opening's own page).
 * The résumé is stored on the private disk under a random name; the
 * visitor returns to the page they sent from with the form's success state.
 */
class JobApplicationController extends Controller
{
    public function store(StoreJobApplicationRequest $request, JobOpening $job): RedirectResponse
    {
        abort_unless($job->is_active, 404);

        $file = $request->file('resume');
        $path = $file->storeAs(
            'resumes/'.now()->format('Y/m'),
            Str::uuid().'.'.strtolower($file->getClientOriginalExtension()),
            JobApplication::DISK,
        );

        JobApplication::create([
            'job_opening_id' => $job->id,
            'job_title' => $job->title,
            'phone' => $request->validated('phone'),
            'resume_path' => $path,
            'resume_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'resume_mime' => (string) $file->getMimeType(),
            'resume_size' => (int) $file->getSize(),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        $back = url()->previous() ?: route('careers');

        return redirect()->to(strtok($back, '#').'#job-'.$job->id)->with('job_applied', $job->id);
    }
}
