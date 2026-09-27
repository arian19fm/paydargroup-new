@extends('layouts.admin')

@php $editing = $job->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$job->title : __('admin.jobs.add'))
@php $breadcrumbs = [['label' => __('admin.nav.jobs'), 'url' => route('admin.jobs.index')], ['label' => $editing ? $job->title : __('admin.create')]]; @endphp

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.jobs.update', $job) : route('admin.jobs.store') }}" class="card" novalidate>
        <div class="card-body" style="max-width: 40rem;">
            @csrf
            @if ($editing) @method('PUT') @endif
            <x-admin.form.input name="title" :label="__('admin.fields.title')" :value="$job->title" required />
            <div class="row">
                <div class="col-md-7"><x-admin.form.input name="category" :label="__('admin.fields.job_category')" :value="$job->category" /></div>
                <div class="col-md-5"><x-admin.form.select name="tone" :label="__('admin.fields.job_tone')" :options="collect(array_keys(App\Models\JobOpening::TONES))->mapWithKeys(fn ($t) => [$t => __('admin.fields.job_tones.'.$t)])->all()" :selected="$job->tone ?? 'blue'" required /></div>
            </div>
            <x-admin.form.textarea name="description" :label="__('admin.fields.job_description')" :value="$job->description" :help="__('admin.fields.job_description_help')" rows="3" />
            <x-admin.form.textarea name="body" :label="__('admin.fields.job_body')" :value="$job->body" :help="__('admin.fields.job_body_help')" rows="14" />
            <x-admin.form.textarea name="specs_text" :label="__('admin.fields.job_specs')" :value="old('specs_text', $job->specsAsText())" :help="__('admin.fields.job_specs_help')" rows="5" />
            <div class="row">
                <div class="col-md-6"><x-admin.form.input name="employment_type" :label="__('admin.fields.employment_type')" :value="$job->employment_type" /></div>
                <div class="col-md-6"><x-admin.form.input name="location" :label="__('admin.fields.job_location')" :value="$job->location" /></div>
            </div>
            <x-admin.form.input name="apply_url" :label="__('admin.fields.apply_url')" :value="$job->apply_url" :help="__('admin.fields.apply_url_help')" dir="ltr" />
            <x-admin.form.input name="sort_order" type="number" min="0" :label="__('admin.fields.sort_order')" :value="$job->sort_order ?? 0" />
            <x-admin.form.checkbox name="is_active" :label="__('admin.fields.is_active')" :checked="$job->is_active ?? true" />
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
            </div>
        </div>
    </form>
@endsection
