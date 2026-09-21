<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleCategoryRequest;
use App\Models\ArticleCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ArticleCategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ArticleCategory::class, 'category');
    }

    public function index(): View
    {
        $categories = ArticleCategory::query()
            ->withCount('articles')
            ->ordered()
            ->paginate(config('cms.per_page'));

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new ArticleCategory(['is_active' => true])]);
    }

    public function store(ArticleCategoryRequest $request): RedirectResponse
    {
        ArticleCategory::create($request->categoryData());

        return redirect()->route('admin.categories.index')->with('success', __('admin.saved'));
    }

    public function edit(ArticleCategory $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(ArticleCategoryRequest $request, ArticleCategory $category): RedirectResponse
    {
        $category->update($request->categoryData());

        return redirect()->route('admin.categories.index')->with('success', __('admin.saved'));
    }

    public function destroy(ArticleCategory $category): RedirectResponse
    {
        // Pivot rows cascade; articles themselves are untouched.
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', __('admin.deleted'));
    }
}
