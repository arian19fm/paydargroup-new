<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesPublishing;
use App\Http\Requests\Admin\Concerns\ValidatesSeoFields;
use App\Models\Page;
use App\Rules\Slug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    use ValidatesPublishing, ValidatesSeoFields;

    public function authorize(): bool
    {
        /** @var Page|null $page */
        $page = $this->route('page');
        $user = $this->user();

        $allowed = $page ? $user->can('update', $page) : $user->can('create', Page::class);

        if ($allowed && $this->wantsPublished()) {
            $allowed = $user->can('publish', $page ?? Page::class);
        }

        return $allowed;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('title', '')),
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    public function rules(): array
    {
        $page = $this->route('page');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', new Slug, Rule::unique('pages', 'slug')->ignore($page)->withoutTrashed()],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'template' => ['nullable', 'string', Rule::in(array_keys(config('cms.page_templates')))],
            'is_featured' => ['boolean'],
            ...$this->publishingRules(),
            ...$this->seoRules(),
        ];
    }

    /** Attributes for the Page model (SEO handled separately via seoData()). */
    public function pageData(): array
    {
        return [
            ...$this->safe()->only(['title', 'slug', 'excerpt', 'content', 'template', 'is_featured']),
            ...$this->publishingData(),
        ];
    }

    public function seoPayload(): array
    {
        return $this->seoData();
    }
}
