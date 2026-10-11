<?php

namespace App\Http\Requests\Admin;

use App\Models\NewsRun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Automatic news settings (admin → اخبار خودکار). */
class NewsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', NewsRun::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'enabled' => $this->boolean('enabled'),
            'auto_publish' => $this->boolean('auto_publish'),
        ]);
    }

    public function rules(): array
    {
        return [
            'enabled' => ['boolean'],
            'run_at' => ['required', 'date_format:H:i'],
            'articles_per_run' => ['required', 'integer', 'min:1', 'max:'.config('news.max_articles_per_run')],
            'auto_publish' => ['boolean'],
            'topics' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['nullable', 'integer', Rule::exists('article_categories', 'id')],
            'author_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'cover_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
        ];
    }

    /** @return array<string, mixed> */
    public function values(): array
    {
        return array_map(fn ($v) => $v === '' ? null : $v, $this->validated());
    }
}
