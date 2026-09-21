@extends('layouts.admin')

@php $editing = $category->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$category->name : __('admin.create'))
@php $breadcrumbs = [['label' => __('admin.nav.categories'), 'url' => route('admin.categories.index')], ['label' => $editing ? $category->name : __('admin.create')]]; @endphp

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="card" novalidate>
        <div class="card-body" style="max-width: 40rem;">
            @csrf
            @if ($editing) @method('PUT') @endif
            <x-admin.form.input name="name" :label="__('admin.fields.name')" :value="$category->name" required />
            <x-admin.form.input name="slug" :label="__('admin.fields.slug')" :value="$category->slug" :help="__('admin.fields.slug_help')" dir="ltr" />
            <x-admin.form.textarea name="description" :label="__('admin.fields.description')" :value="$category->description" rows="3" />
            <x-admin.form.input name="sort_order" type="number" min="0" :label="__('admin.fields.sort_order')" :value="$category->sort_order ?? 0" />
            <x-admin.form.checkbox name="is_active" :label="__('admin.fields.is_active')" :checked="$category->is_active ?? true" />
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
            </div>
        </div>
    </form>
@endsection
