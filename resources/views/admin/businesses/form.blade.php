@extends('layouts.admin')

@php $editing = $business->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$business->title : __('admin.businesses.add'))
@php $breadcrumbs = [['label' => __('admin.nav.businesses'), 'url' => route('admin.businesses.index')], ['label' => $editing ? $business->title : __('admin.create')]]; @endphp

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.businesses.update', $business) : route('admin.businesses.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4"><div class="card-body">
                    <x-admin.form.input name="title" :label="__('admin.fields.title')" :value="$business->title" required />
                    <x-admin.form.input name="slug" :label="__('admin.fields.slug')" :value="$business->slug" :help="__('admin.fields.slug_help')" dir="ltr" />
                    <x-admin.form.input name="eyebrow" :label="__('admin.fields.eyebrow')" :value="$business->eyebrow" :help="__('admin.fields.eyebrow_help')" />
                    <x-admin.form.input name="tagline" :label="__('admin.fields.tagline')" :value="$business->tagline" :help="__('admin.fields.tagline_help')" />
                    <x-admin.form.textarea name="features_text" :label="__('admin.fields.features')" :value="old('features_text', implode(PHP_EOL, $business->featureList()))" :help="__('admin.fields.features_help')" rows="5" />
                    <x-admin.form.textarea name="excerpt" :label="__('admin.fields.excerpt')" :value="$business->excerpt" :help="__('admin.fields.business_excerpt_help')" rows="3" />
                    <x-admin.form.textarea name="content" :label="__('admin.fields.content')" :value="$business->content" :help="__('admin.fields.content_help')" rows="14" />
                    <x-admin.form.input name="website_url" type="url" :label="__('admin.fields.website_url')" :value="$business->website_url" :help="__('admin.fields.website_url_help')" dir="ltr" />
                </div></div>
                <fieldset class="card mb-4">
                    <legend class="card-header h6 mb-0 py-2">{{ __('admin.businesses.benefits_section') }}</legend>
                    <div class="card-body">
                        <p class="form-text mt-0">{{ __('admin.businesses.benefits_help') }}</p>
                        <x-admin.form.input name="benefits_title" :label="__('admin.fields.benefits_title')" :value="$business->benefits_title" :help="__('admin.fields.benefits_title_help')" />
                        <x-admin.form.textarea name="benefits_text" :label="__('admin.fields.benefits_text')" :value="$business->benefits_text" rows="2" />
                        <x-admin.form.textarea name="benefits_text_lines" :label="__('admin.fields.benefits')" :value="old('benefits_text_lines', $business->benefitsAsText())" :help="__('admin.fields.benefits_help')" rows="5" />
                        @if ($business->benefitsMedia)
                            <div class="d-flex align-items-center gap-3 mb-2">
                                @if ($business->benefitsMedia->isImage())
                                    <img src="{{ $business->benefitsMedia->url() }}" width="96" height="48" alt="" class="rounded border object-fit-cover bg-body-tertiary">
                                @endif
                                <div class="small">
                                    <div>{{ __('admin.fields.benefits_media_current') }}: <a href="{{ route('admin.media.edit', $business->benefitsMedia) }}">{{ $business->benefitsMedia->original_filename }}</a></div>
                                    <x-admin.form.checkbox name="remove_benefits_media" :label="__('admin.fields.remove_benefits_media')" />
                                </div>
                            </div>
                        @endif
                        <x-admin.form.input name="benefits_media" type="file" accept="image/png,image/jpeg,image/webp,video/mp4,video/webm" :label="__('admin.fields.benefits_media_upload')" :help="__('admin.fields.benefits_media_help')" />
                        <x-admin.form.input name="benefits_media_id" type="number" min="0" :label="__('admin.fields.image_media_id')" :value="$business->benefits_media_id" />

                        <hr>
                        <p class="form-text mt-0">{{ __('admin.fields.benefits_poster_help') }}</p>
                        @if ($business->benefitsPoster)
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="{{ $business->benefitsPoster->url() }}" width="96" height="48" alt="" class="rounded border object-fit-cover bg-body-tertiary">
                                <div class="small">
                                    <div>{{ __('admin.fields.benefits_poster_current') }}: <a href="{{ route('admin.media.edit', $business->benefitsPoster) }}">{{ $business->benefitsPoster->original_filename }}</a></div>
                                    <x-admin.form.checkbox name="remove_benefits_poster" :label="__('admin.fields.remove_benefits_poster')" />
                                </div>
                            </div>
                        @endif
                        <x-admin.form.input name="benefits_poster" type="file" accept="image/png,image/jpeg,image/webp" :label="__('admin.fields.benefits_poster_upload')" />
                        <x-admin.form.input name="benefits_poster_media_id" type="number" min="0" :label="__('admin.fields.image_media_id')" :value="$business->benefits_poster_media_id" />
                    </div>
                </fieldset>
                <x-admin.seo-fields :seo="$business->seo" />
            </div>
            <div class="col-lg-4">
                <div class="card"><div class="card-body">
                    <x-admin.form.select name="status" :label="__('admin.status.label')" :options="collect(App\Enums\ContentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()" :selected="$business->status?->value ?? 'draft'" required />
                    <x-admin.form.input name="published_at" type="datetime-local" :label="__('admin.fields.published_at')" :value="$business->published_at?->format('Y-m-d\TH:i')" :help="__('admin.fields.published_at_help')" dir="ltr" />
                    <x-admin.form.select name="accent" :label="__('admin.fields.accent')" :options="collect(App\Models\Business::ACCENTS)->mapWithKeys(fn ($a) => [$a => __('admin.fields.accents.'.$a)])->all()" :selected="$business->accent ?? 'fund'" required />
                    <x-admin.form.input name="sort_order" type="number" min="0" :label="__('admin.fields.sort_order')" :value="$business->sort_order ?? 0" />

                    <fieldset class="mb-3">
                        <legend class="form-label fs-6">{{ __('admin.fields.image') }}</legend>
                        @if ($business->image)
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="{{ $business->image->url() }}" width="96" height="72" alt="" class="rounded border object-fit-cover bg-body-tertiary">
                                <div class="small">
                                    <div>{{ __('admin.fields.image_current') }}: <a href="{{ route('admin.media.edit', $business->image) }}">{{ $business->image->original_filename }}</a></div>
                                    <x-admin.form.checkbox name="remove_image" :label="__('admin.fields.remove_image')" />
                                </div>
                            </div>
                        @endif
                        <x-admin.form.input name="image" type="file" accept="image/png,image/jpeg,image/webp" :label="__('admin.fields.image_upload')" :help="__('admin.fields.image_help')" />
                        <x-admin.form.input name="image_media_id" type="number" min="0" :label="__('admin.fields.image_media_id')" :value="$business->image_media_id" />
                    </fieldset>

                    @if ($editing && $business->isPublished())
                        <p class="small"><a href="{{ $business->publicUrl() }}" target="_blank" rel="noopener">{{ __('admin.view_site') }}</a></p>
                    @endif
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                        <a href="{{ route('admin.businesses.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
                    </div>
                </div></div>
            </div>
        </div>
    </form>
@endsection
