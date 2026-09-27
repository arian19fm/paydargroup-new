<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use App\Support\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(protected MediaService $media)
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
        $article = Article::create($this->withUploadedImage($request, $request->articleData()));
        $article->categories()->sync($request->categoryIds());
        $article->saveSeo($request->seoPayload());

        return redirect()->route('admin.articles.edit', $article)->with('success', __('admin.saved'));
    }

    public function edit(Article $article): View
    {
        $article->load(['seo', 'categories:id', 'featuredImage']);

        return view('admin.articles.form', ['article' => $article, ...$this->formOptions()]);
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $article->update($this->withUploadedImage($request, $request->articleData()));
        $article->categories()->sync($request->categoryIds());
        $article->saveSeo($request->seoPayload());

        return redirect()->route('admin.articles.edit', $article)->with('success', __('admin.saved'));
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', __('admin.deleted'));
    }

    /** A cover uploaded from the form goes into the media library, named after the article. */
    protected function withUploadedImage(ArticleRequest $request, array $data): array
    {
        if ($request->hasFile('featured_image')) {
            $data['featured_image_id'] = $this->media->upload($request->file('featured_image'), $request->user(), [
                'title' => $data['title'],
                'alt_text' => $data['title'],
            ])->id;
        }

        return $data;
    }

    protected function formOptions(): array
    {
        return [
            'categories' => ArticleCategory::query()->ordered()->get(['id', 'name']),
            'authors' => User::query()->active()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
