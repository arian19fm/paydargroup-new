<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\SanitizesRichContent;
use App\Http\Requests\Admin\Concerns\ValidatesPublishing;
use App\Http\Requests\Admin\Concerns\ValidatesSeoFields;
use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{
    use SanitizesRichContent, ValidatesPublishing, ValidatesSeoFields;

    public function authorize(): bool
    {
        /** @var Page|null $page */
        $page = $this->route('page');
        $user = $this->user();

        $allowed = $page && $user->can('update', $page);

        if ($allowed && $this->wantsPublished()) {
            $allowed = $user->can('publish', $page);
        }

        return $allowed;
    }

    protected function prepareForValidation(): void
    {
        $this->sanitizeRichContent('content');
        $this->merge(['is_featured' => $this->boolean('is_featured')]);
    }

    public function rules(): array
    {
        // Slug and template are fixed (the URL and design of the page).
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => $this->richContentRules(),
            'is_featured' => ['boolean'],
            ...$this->publishingRules(),
            ...$this->seoRules(),
        ];
    }

    /** Attributes for the Page model (SEO handled separately via seoData()). */
    public function pageData(): array
    {
        return [
            ...$this->safe()->only(['title', 'excerpt', 'content', 'is_featured']),
            ...$this->publishingData(),
        ];
    }

    public function seoPayload(): array
    {
        return $this->seoData();
    }
}
