<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Article::class, 'article');
    }

    public function index(Request $request): View
    {
        $articles = Article::query()
            ->with(['author:id,name', 'categories:id,name'])
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%")))
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate(config('cms.per_page'))
            ->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    public function create(): View
    {
        return view('admin.articles.form', [
            'article' => new Article(['author_id' => auth()->id()]),
            ...$this->formOptions(),
        ]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $article = Article::create($request->articleData());
        $article->categories()->sync($request->categoryIds());
        $article->saveSeo($request->seoPayload());

        return redirect()->route('admin.articles.edit', $article)->with('success', __('admin.saved'));
    }

    public function edit(Article $article): View
    {
        $article->load(['seo', 'categories:id']);

        return view('admin.articles.form', ['article' => $article, ...$this->formOptions()]);
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $article->update($request->articleData());
        $article->categories()->sync($request->categoryIds());
        $article->saveSeo($request->seoPayload());

        return redirect()->route('admin.articles.edit', $article)->with('success', __('admin.saved'));
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', __('admin.deleted'));
    }

    protected function formOptions(): array
    {
        return [
            'categories' => ArticleCategory::query()->ordered()->get(['id', 'name']),
            'authors' => User::query()->active()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
