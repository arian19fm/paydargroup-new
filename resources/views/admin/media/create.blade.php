@extends('layouts.admin')

@section('title', __('admin.media.upload'))
@php $breadcrumbs = [['label' => __('admin.nav.media'), 'url' => route('admin.media.index')], ['label' => __('admin.media.upload')]]; @endphp

@section('content')
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="card" novalidate>
        <div class="card-body" style="max-width: 40rem;">
            @csrf
            <div class="mb-3">
                <label for="file" class="form-label">{{ __('admin.fields.file') }} <span class="text-danger" aria-hidden="true">*</span></label>
                <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf" required aria-describedby="file-help">
                <div id="file-help" class="form-text">{{ __('admin.fields.file_help') }}</div>
                @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <x-admin.form.input name="alt_text" :label="__('admin.fields.alt_text')" :help="__('admin.fields.alt_text_help')" />
            <x-admin.form.input name="title" :label="__('admin.fields.title')" />
            <x-admin.form.textarea name="caption" :label="__('admin.fields.caption')" rows="2" />
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.media.upload') }}</button>
                <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
            </div>
        </div>
    </form>
@endsection
