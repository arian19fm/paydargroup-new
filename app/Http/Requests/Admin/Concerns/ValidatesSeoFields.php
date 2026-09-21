<?php

namespace App\Http\Requests\Admin\Concerns;

use Illuminate\Validation\Rule;

/**
 * Validation rules for the reusable SEO form section (fields nested under
 * `seo.*`). Limits are structural (column sizes / sane URLs); length
 * guidance for titles and descriptions is advisory in the UI, not enforced.
 */
trait ValidatesSeoFields
{
    protected function seoRules(): array
    {
        return [
            'seo' => ['nullable', 'array'],
            'seo.title' => ['nullable', 'string', 'max:255'],
            'seo.description' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'string', 'url:http,https', 'max:2048'],
            'seo.robots_index' => ['nullable', 'boolean'],
            'seo.robots_follow' => ['nullable', 'boolean'],
            'seo.og_title' => ['nullable', 'string', 'max:255'],
            'seo.og_description' => ['nullable', 'string', 'max:500'],
            'seo.og_image_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'seo.twitter_title' => ['nullable', 'string', 'max:255'],
            'seo.twitter_description' => ['nullable', 'string', 'max:500'],
            'seo.twitter_image_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'seo.schema_type' => ['nullable', 'string', Rule::in(config('cms.schema_types'))],
        ];
    }

    /** The SEO payload with the index/follow checkboxes normalised. */
    protected function seoData(): array
    {
        $seo = $this->validated('seo', []) ?? [];

        // Unchecked checkboxes are absent from the payload; the form always
        // submits the section, so absent means false.
        $seo['robots_index'] = $this->boolean('seo.robots_index');
        $seo['robots_follow'] = $this->boolean('seo.robots_follow');

        return $seo;
    }
}
