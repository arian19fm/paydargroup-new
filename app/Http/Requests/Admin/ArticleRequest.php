<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesPublishing;
use App\Http\Requests\Admin\Concerns\ValidatesSeoFields;
use App\Models\Article;
use App\Rules\Slug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    use ValidatesPublishing, ValidatesSeoFields;

    public function authorize(): bool
    {
        /** @var Article|null $article */
        $article = $this->route('article');
        $user = $this->user();

        $allowed = $article ? $user->can('update', $article) : $user->can('create', Article::class);

        if ($allowed && $this->wantsPublished()) {
            $allowed = $user->can('publish', $article ?? Article::class);
        }

        return $allowed;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('title', '')),
        ]);
    }

    public function rules(): array
    {
        $article = $this->route('article');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', new Slug(checkReserved: false), Rule::unique('articles', 'slug')->ignore($article)->withoutTrashed()],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'featured_image_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'author_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('article_categories', 'id')],
            ...$this->publishingRules(),
            ...$this->seoRules(),
        ];
    }

    public function articleData(): array
    {
        return [
            ...$this->safe()->only(['title', 'slug', 'excerpt', 'content', 'featured_image_id', 'author_id']),
            ...$this->publishingData(),
        ];
    }

    /** @return list<int> */
    public function categoryIds(): array
    {
        return array_map('intval', $this->validated('categories', []) ?? []);
    }

    public function seoPayload(): array
    {
        return $this->seoData();
    }
}
