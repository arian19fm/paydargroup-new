<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaStoreRequest;
use App\Http\Requests\Admin\MediaUpdateRequest;
use App\Models\Media;
use App\Support\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(protected MediaService $media)
    {
        $this->authorizeResource(Media::class, 'medium');
    }

    public function index(Request $request): View
    {
        $items = Media::query()
            ->with('uploader:id,name')
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('original_filename', 'like', "%{$term}%")
                ->orWhere('alt_text', 'like', "%{$term}%")
                ->orWhere('title', 'like', "%{$term}%")))
            ->latest()
            ->paginate(config('cms.per_page'))
            ->withQueryString();

        return view('admin.media.index', ['media' => $items]);
    }

    public function create(): View
    {
        return view('admin.media.create');
    }

    public function store(MediaStoreRequest $request): RedirectResponse
    {
        $medium = $this->media->upload(
            $request->file('file'),
            $request->user(),
            $request->safe()->only(['alt_text', 'title', 'caption'])
        );

        return redirect()->route('admin.media.edit', $medium)->with('success', __('admin.media.uploaded'));
    }

    public function edit(Media $medium): View
    {
        return view('admin.media.edit', compact('medium'));
    }

    public function update(MediaUpdateRequest $request, Media $medium): RedirectResponse
    {
        $medium->update($request->validated());

        return redirect()->route('admin.media.edit', $medium)->with('success', __('admin.saved'));
    }

    public function destroy(Media $medium): RedirectResponse
    {
        // References (featured images, SEO images, settings) are nulled by
        // the database; the file is removed by the model's deleted hook.
        $medium->delete();

        return redirect()->route('admin.media.index')->with('success', __('admin.deleted'));
    }
}
