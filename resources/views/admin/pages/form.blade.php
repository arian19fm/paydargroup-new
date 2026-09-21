@extends('layouts.admin')

@php $editing = $page->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$page->title : __('admin.create'))
@php $breadcrumbs = [['label' => __('admin.nav.pages'), 'url' => route('admin.pages.index')], ['label' => $editing ? $page->title : __('admin.create')]]; @endphp

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.pages.update', $page) : route('admin.pages.store') }}" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4"><div class="card-body">
                    <x-admin.form.input name="title" :label="__('admin.fields.title')" :value="$page->title" required />
                    <x-admin.form.input name="slug" :label="__('admin.fields.slug')" :value="$page->slug" :help="__('admin.fields.slug_help')" dir="ltr" />
                    <x-admin.form.textarea name="excerpt" :label="__('admin.fields.excerpt')" :value="$page->excerpt" rows="2" />
                    <x-admin.form.textarea name="content" :label="__('admin.fields.content')" :value="$page->content" :help="__('admin.fields.content_help')" rows="14" />
                </div></div>
                <x-admin.seo-fields :seo="$page->seo" />
            </div>
            <div class="col-lg-4">
                <div class="card"><div class="card-body">
                    <x-admin.form.select name="status" :label="__('admin.status.label')" :options="collect(App\Enums\ContentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()" :selected="$page->status?->value ?? 'draft'" required />
                    <x-admin.form.input name="published_at" type="datetime-local" :label="__('admin.fields.published_at')" :value="$page->published_at?->format('Y-m-d\TH:i')" :help="__('admin.fields.published_at_help')" dir="ltr" />
                    <x-admin.form.select name="template" :label="__('admin.fields.template')" :options="array_combine(array_keys(config('cms.page_templates')), array_keys(config('cms.page_templates')))" :selected="$page->template" :placeholder="__('admin.none')" />
                    <x-admin.form.checkbox name="is_featured" :label="__('admin.fields.is_featured')" :checked="$page->is_featured" />
                    @if ($editing && $page->isPublished())
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
