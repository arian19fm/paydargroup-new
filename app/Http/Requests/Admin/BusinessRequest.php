<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesPublishing;
use App\Http\Requests\Admin\Concerns\ValidatesSeoFields;
use App\Models\Business;
use App\Rules\Slug;
use App\Support\Media\MediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class BusinessRequest extends FormRequest
{
    use ValidatesPublishing, ValidatesSeoFields;

    public function authorize(): bool
    {
        /** @var Business|null $business */
        $business = $this->route('business');
        $user = $this->user();

        $allowed = $business ? $user->can('update', $business) : $user->can('create', Business::class);

        if ($allowed && $this->wantsPublished()) {
            $allowed = $user->can('publish', $business ?? Business::class);
        }

        return $allowed;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('title', '')),
            'remove_image' => $this->boolean('remove_image'),
            'image_media_id' => $this->input('image_media_id') ?: null,
            'remove_benefits_media' => $this->boolean('remove_benefits_media'),
            'benefits_media_id' => $this->input('benefits_media_id') ?: null,
            'remove_benefits_poster' => $this->boolean('remove_benefits_poster'),
            'benefits_poster_media_id' => $this->input('benefits_poster_media_id') ?: null,
            'benefits' => Business::parseBenefits((string) $this->input('benefits_text_lines', '')),
            'website_url' => trim((string) $this->input('website_url')) ?: null,
            // One feature per line in the form.
            'features' => array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $this->input('features_text', ''))))),
        ]);
    }

    public function rules(): array
    {
        $business = $this->route('business');

        return [
            'title' => ['required', 'string', 'max:255'],
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', new Slug(checkReserved: false), Rule::unique('businesses', 'slug')->ignore($business)->withoutTrashed()],
            'tagline' => ['nullable', 'string', 'max:500'],
            'features' => ['nullable', 'array', 'max:12'],
            'features.*' => ['string', 'max:100'],
            'accent' => ['required', Rule::in(Business::ACCENTS)],
            'image' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp'])->max(MediaService::MAX_KILOBYTES), 'mimetypes:image/jpeg,image/png,image/webp'],
            'image_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'remove_image' => ['boolean'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'website_url' => ['nullable', 'string', 'url:http,https', 'max:500'],
            'benefits_title' => ['nullable', 'string', 'max:255'],
            'benefits_text' => ['nullable', 'string', 'max:1000'],
            'benefits' => ['nullable', 'array', 'max:8'],
            'benefits.*.title' => ['required', 'string', 'max:120'],
            'benefits.*.description' => ['nullable', 'string', 'max:300'],
            'benefits_media' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm'])->max(MediaService::MAX_VIDEO_KILOBYTES), 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm'],
            'benefits_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'benefits_poster' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp'])->max(MediaService::MAX_KILOBYTES), 'mimetypes:image/jpeg,image/png,image/webp'],
            'benefits_poster_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'remove_benefits_poster' => ['boolean'],
            'remove_benefits_media' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            ...$this->publishingRules(),
            ...$this->seoRules(),
        ];
    }

    /** Column values only; the uploaded file and the remove flag are handled by the controller. */
    public function businessData(): array
    {
        $data = [
            ...$this->safe()->only(['title', 'slug', 'eyebrow', 'tagline', 'features', 'accent', 'image_media_id', 'excerpt', 'content', 'website_url', 'benefits_title', 'benefits_text', 'benefits', 'benefits_media_id', 'benefits_poster_media_id']),
            ...$this->publishingData(),
        ];
        $data['features'] = array_values($data['features'] ?? []);
        $data['benefits'] = array_values($data['benefits'] ?? []);
        $data['sort_order'] = (int) ($this->validated('sort_order') ?? 0);

        if ($this->boolean('remove_image')) {
            $data['image_media_id'] = null;
        }

        if ($this->boolean('remove_benefits_media')) {
            $data['benefits_media_id'] = null;
        }

        if ($this->boolean('remove_benefits_poster')) {
            $data['benefits_poster_media_id'] = null;
        }

        return $data;
    }

    public function seoPayload(): array
    {
        return $this->seoData();
    }
}
