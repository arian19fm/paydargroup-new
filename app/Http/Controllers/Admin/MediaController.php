<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaStoreRequest;
use App\Http\Requests\Admin\MediaUpdateRequest;
use App\Models\Media;
use App\Support\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;

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

    /**
     * Image upload from the rich editor (resources/js/admin/rich-editor):
     * stored in the library as a WebP content image, answered with what the editor
     * needs to insert an <img> with its alt text and dimensions.
     */
    public function editorImage(Request $request): JsonResponse
    {
        $this->authorize('create', Media::class);

        $data = $request->validate([
            'file' => ['required', File::types(['jpg', 'jpeg', 'png', 'webp', 'gif'])->max(MediaService::MAX_KILOBYTES), 'mimetypes:image/jpeg,image/png,image/webp,image/gif'],
            'alt_text' => ['nullable', 'string', 'max:500'],
        ]);

        $medium = $this->media->uploadContentImage($request->file('file'), $request->user(), ['alt_text' => $data['alt_text'] ?? null]);

        return response()->json([
            'id' => $medium->id,
            'url' => $medium->url(),
            'width' => $medium->width,
            'height' => $medium->height,
            'alt' => $medium->alt_text,
        ], 201);
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
