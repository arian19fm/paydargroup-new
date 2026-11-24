<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusinessRequest;
use App\Models\Business;
use App\Support\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
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
        $business = Business::create($this->withAppFile($request, $this->withUploadedImage($request, $request->businessData())));
        $business->saveSeo($request->seoPayload());

        return redirect()->route('admin.businesses.edit', $business)->with('success', __('admin.saved'));
    }

    public function edit(Business $business): View
    {
        $business->load(['seo', 'image', 'benefitsMedia', 'benefitsPoster', 'appLogo', 'appQr']);

        return view('admin.businesses.form', compact('business'));
    }

    public function update(BusinessRequest $request, Business $business): RedirectResponse
    {
        $business->update($this->withAppFile($request, $this->withUploadedImage($request, $request->businessData()), $business));
        $business->saveSeo($request->seoPayload());

        return redirect()->route('admin.businesses.edit', $business)->with('success', __('admin.saved'));
    }

    public function destroy(Business $business): RedirectResponse
    {
        $business->delete();

        return redirect()->route('admin.businesses.index')->with('success', __('admin.deleted'));
    }

    /** Files uploaded from the form go into the media library, named after the business. */
    protected function withUploadedImage(BusinessRequest $request, array $data): array
    {
        foreach (['image' => 'image_media_id', 'benefits_media' => 'benefits_media_id', 'benefits_poster' => 'benefits_poster_media_id', 'app_logo' => 'app_logo_media_id', 'app_qr' => 'app_qr_media_id'] as $field => $column) {
            if ($request->hasFile($field)) {
                $data[$column] = $this->media->upload($request->file($field), $request->user(), [
                    'title' => $data['title'],
                    'alt_text' => $data['title'],
                ])->id;
            }
        }

        return $data;
    }

    /**
     * The Android install file lives on the public disk under apps/, named
     * after the business so the download keeps a readable name. A new upload
     * or the remove flag deletes the previous file.
     */
    protected function withAppFile(BusinessRequest $request, array $data, ?Business $business = null): array
    {
        $disk = Storage::disk(Business::APP_DISK);
        $previous = $business?->app_android_path;

        if ($request->hasFile('app_android_file')) {
            $file = $request->file('app_android_file');
            $data['app_android_path'] = $file->storeAs('apps', $data['slug'].'-'.Str::lower(Str::random(8)).'.apk', ['disk' => Business::APP_DISK]);
            $data['app_android_size'] = $file->getSize();
        } elseif ($request->boolean('remove_app_android_file')) {
            $data['app_android_path'] = null;
            $data['app_android_size'] = null;
        } else {
            return $data;
        }

        if ($previous && $previous !== $data['app_android_path']) {
            $disk->delete($previous);
        }

        return $data;
    }
}
