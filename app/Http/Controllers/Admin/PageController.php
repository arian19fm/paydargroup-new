<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The site's pages are fixed: they are listed and edited here but never
 * created or deleted (config/cms.php → fixed_pages; the
 * ensure_fixed_pages migration guarantees they exist).
 */
class PageController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Page::class, 'page');
    }

    public function index(Request $request): View
    {
        $pages = Page::query()
            ->with('editor:id,name')
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%")))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate(config('cms.per_page'))
            ->withQueryString();

        return view('admin.pages.index', compact('pages'));
    }

    public function edit(Page $page): View
    {
        $page->load('seo');

        return view('admin.pages.form', compact('page'));
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $page->update($request->pageData());
        $page->saveSeo($request->seoPayload());

        return redirect()->route('admin.pages.edit', $page)->with('success', __('admin.saved'));
    }
}
