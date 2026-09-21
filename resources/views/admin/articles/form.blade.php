@extends('layouts.admin')

@php $editing = $article->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$article->title : __('admin.create'))
@php $breadcrumbs = [['label' => __('admin.nav.articles'), 'url' => route('admin.articles.index')], ['label' => $editing ? $article->title : __('admin.create')]]; @endphp

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.articles.update', $article) : route('admin.articles.store') }}" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4"><div class="card-body">
                    <x-admin.form.input name="title" :label="__('admin.fields.title')" :value="$article->title" required />
                    <x-admin.form.input name="slug" :label="__('admin.fields.slug')" :value="$article->slug" :help="__('admin.fields.slug_help')" dir="ltr" />
                    <x-admin.form.textarea name="excerpt" :label="__('admin.fields.excerpt')" :value="$article->excerpt" rows="2" />
                    <x-admin.form.textarea name="content" :label="__('admin.fields.content')" :value="$article->content" :help="__('admin.fields.content_help')" rows="16" required />
                </div></div>
                <x-admin.seo-fields :seo="$article->seo" />
            </div>
            <div class="col-lg-4">
                <div class="card"><div class="card-body">
                    <x-admin.form.select name="status" :label="__('admin.status.label')" :options="collect(App\Enums\ContentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()" :selected="$article->status?->value ?? 'draft'" required />
                    <x-admin.form.input name="published_at" type="datetime-local" :label="__('admin.fields.published_at')" :value="$article->published_at?->format('Y-m-d\TH:i')" :help="__('admin.fields.published_at_help')" dir="ltr" />
                    <x-admin.form.select name="categories[]" :label="__('admin.fields.categories')" :options="$categories->pluck('name', 'id')->all()" :selected="$article->categories->pluck('id')->all()" multiple size="5" />
                    <x-admin.form.select name="author_id" :label="__('admin.fields.author')" :options="$authors->pluck('name', 'id')->all()" :selected="$article->author_id" :placeholder="__('admin.none')" />
                    <x-admin.form.input name="featured_image_id" type="number" min="1" :label="__('admin.fields.featured_image')" :value="$article->featured_image_id" :help="__('admin.fields.featured_image_help')" />
                    @if ($editing && $article->isPublished())
                        <p class="small"><a href="{{ $article->publicUrl() }}" target="_blank" rel="noopener">{{ __('admin.view_site') }}</a></p>
                    @endif
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                        <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
                    </div>
                </div></div>
            </div>
        </div>
    </form>
@endsection
