@extends('layouts.admin')

{{-- Pages are fixed: only edited here; the address and template are shown, not editable. --}}
@php
    $breadcrumbs = [['label' => __('admin.nav.pages'), 'url' => route('admin.pages.index')], ['label' => $page->title]];
    $template = $page->template ?: 'default';
@endphp
@section('title', __('admin.edit').': '.$page->title)

@section('content')
    <form method="POST" action="{{ route('admin.pages.update', $page) }}" novalidate>
        @csrf
        @method('PUT')
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4"><div class="card-body">
                    <x-admin.form.input name="title" :label="__('admin.fields.title')" :value="$page->title" required />
                    <p class="form-text mt-n2 mb-3">{{ __('admin.pages.address') }}: <code dir="ltr">/{{ $page->slug }}</code></p>
                    <x-admin.form.textarea name="excerpt" :label="__('admin.fields.excerpt')" :value="$page->excerpt" rows="2" />
                    <x-admin.form.textarea name="content" :label="__('admin.fields.content')" :value="$page->content" :help="__('admin.fields.content_help')" rows="14" />
                </div></div>
                <x-admin.seo-fields :seo="$page->seo" />
            </div>
            <div class="col-lg-4">
                <div class="card"><div class="card-body">
                    <x-admin.form.select name="status" :label="__('admin.status.label')" :options="collect(App\Enums\ContentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()" :selected="$page->status?->value ?? 'draft'" required />
                    <x-admin.form.input name="published_at" type="datetime-local" :label="__('admin.fields.published_at')" :value="$page->published_at?->format('Y-m-d\TH:i')" :help="__('admin.fields.published_at_help')" dir="ltr" />
                    <p class="small text-body-secondary">{{ __('admin.fields.template') }}: {{ __('admin.pages.templates.'.$template) }}</p>
                    @if ($template === 'about')
                        <p class="small">@can('settings.view')<a href="{{ route('admin.settings.edit', 'about') }}">{{ __('admin.pages.about_extras') }}</a>@else{{ __('admin.pages.about_extras') }}@endcan</p>
                    @endif
                    <x-admin.form.checkbox name="is_featured" :label="__('admin.fields.is_featured')" :checked="$page->is_featured" />
                    @if ($page->isPublished())
                        <p class="small"><a href="{{ $page->publicUrl() }}" target="_blank" rel="noopener">{{ __('admin.view_site') }}</a></p>
                    @endif
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
                    </div>
                </div></div>
            </div>
        </div>
    </form>
@endsection
