<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusinessRequest;
use App\Models\Business;
use App\Support\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class BusinessController extends Controller
{
    /** Files stored by the current request: [disk, path] pairs, removed again if the save fails. */
    protected array $stored = [];

    public function __construct(protected MediaService $media)
    {
        $this->authorizeResource(Business::class, 'business');
    }

    public function index(Request $request): View
    {
        $businesses = Business::query()
            ->with('image:id,disk,path,original_filename')
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%")))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->ordered()
            ->paginate(config('cms.per_page'))
            ->withQueryString();

        return view('admin.businesses.index', compact('businesses'));
    }

    public function create(): View
    {
        return view('admin.businesses.form', ['business' => new Business(['accent' => Business::ACCENTS[0]])]);
    }

    public function store(BusinessRequest $request): RedirectResponse
    {
        $business = $this->persist($request, new Business);

        return redirect()->route('admin.businesses.edit', $business)->with('success', __('admin.saved'));
    }

    public function edit(Business $business): View
    {
        $business->load(['seo', 'image', 'benefitsMedia', 'benefitsPoster', 'appLogo', 'appQr']);

        return view('admin.businesses.form', compact('business'));
    }

    public function update(BusinessRequest $request, Business $business): RedirectResponse
    {
        $this->persist($request, $business);

        return redirect()->route('admin.businesses.edit', $business)->with('success', __('admin.saved'));
    }

    public function destroy(Business $business): RedirectResponse
    {
        $business->delete();

        return redirect()->route('admin.businesses.index')->with('success', __('admin.deleted'));
    }

    /**
     * Store the uploads and save the business as one unit. The rows are in
     * a transaction; files are not, so when the save fails the files this
     * request stored are removed again (their media rows roll back) and
     * nothing is left orphaned. The Android file being replaced is deleted
     * only once the save has succeeded.
     */
    protected function persist(BusinessRequest $request, Business $business): Business
    {
        $previousApp = $business->app_android_path;
        $this->stored = [];

        try {
            DB::transaction(function () use ($request, $business) {
                $data = $this->withAppFile($request, $this->withUploadedImage($request, $request->businessData()));
                $business->fill($data)->save();
                $business->saveSeo($request->seoPayload());
            });
        } catch (Throwable $e) {
            foreach ($this->stored as [$disk, $path]) {
                Storage::disk($disk)->delete($path);
            }

            throw $e;
        }

        if ($previousApp && $previousApp !== $business->app_android_path) {
            Storage::disk(Business::APP_DISK)->delete($previousApp);
        }

        return $business;
    }

    /** Files uploaded from the form go into the media library, named after the business. */
    protected function withUploadedImage(BusinessRequest $request, array $data): array
    {
        foreach (['image' => 'image_media_id', 'benefits_media' => 'benefits_media_id', 'benefits_poster' => 'benefits_poster_media_id', 'app_logo' => 'app_logo_media_id', 'app_qr' => 'app_qr_media_id'] as $field => $column) {
            if ($request->hasFile($field)) {
                $media = $this->media->upload($request->file($field), $request->user(), [
                    'title' => $data['title'],
                    'alt_text' => $data['title'],
                ]);
                $this->stored[] = [$media->disk, $media->path];
                $data[$column] = $media->id;
            }
        }

        return $data;
    }

    /**
     * The Android install file lives on the public disk under apps/, named
     * after the business so the download keeps a readable name. The file it
     * replaces is removed by persist() after the save.
     */
    protected function withAppFile(BusinessRequest $request, array $data): array
    {
        if ($request->hasFile('app_android_file')) {
            $file = $request->file('app_android_file');
            $data['app_android_path'] = $file->storeAs('apps', $data['slug'].'-'.Str::lower(Str::random(8)).'.apk', ['disk' => Business::APP_DISK]);
            $data['app_android_size'] = $file->getSize();
            $this->stored[] = [Business::APP_DISK, $data['app_android_path']];
        } elseif ($request->boolean('remove_app_android_file')) {
            $data['app_android_path'] = null;
            $data['app_android_size'] = null;
        }

        return $data;
    }
}
