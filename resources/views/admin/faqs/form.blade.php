@extends('layouts.admin')

@php
    $editing = $faq->exists;
    $breadcrumbs = [['label' => __('admin.nav.faqs'), 'url' => route('admin.faqs.index')], ['label' => $editing ? \Illuminate\Support\Str::limit($faq->question, 40) : __('admin.create')]];
@endphp
@section('title', $editing ? __('admin.edit').': '.\Illuminate\Support\Str::limit($faq->question, 60) : __('admin.faqs.add'))

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="card" novalidate>
        <div class="card-body" style="max-width: 48rem;">
            @csrf
            @if ($editing) @method('PUT') @endif
            <x-admin.form.input name="question" :label="__('admin.fields.question')" :value="$faq->question" maxlength="500" required />
            <x-admin.form.textarea name="answer" :label="__('admin.fields.answer')" :value="$faq->answer" rows="6" maxlength="5000" :help="__('admin.faqs.answer_help')" />
            <x-admin.form.input name="sort_order" type="number" min="0" :label="__('admin.fields.sort_order')" :value="$faq->sort_order ?? 0" />
            <x-admin.form.checkbox name="is_active" :label="__('admin.fields.is_active')" :checked="$faq->is_active ?? true" />
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
            </div>
        </div>
    </form>
@endsection
