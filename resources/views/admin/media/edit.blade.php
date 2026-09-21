@extends('layouts.admin')

@section('title', __('admin.edit').': '.$medium->original_filename)
@php $breadcrumbs = [['label' => __('admin.nav.media'), 'url' => route('admin.media.index')], ['label' => $medium->original_filename]]; @endphp

@section('content')
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card"><div class="card-body">
                @if ($medium->isImage())
                    <img src="{{ $medium->url() }}" alt="{{ $medium->alt_text }}" width="{{ $medium->width }}" height="{{ $medium->height }}" class="img-fluid rounded border mb-3">
                @endif
                <dl class="row small mb-0">
                    <dt class="col-4">ID</dt><dd class="col-8"><code>{{ $medium->id }}</code></dd>
                    <dt class="col-4">{{ __('admin.media.copy_url') }}</dt><dd class="col-8"><input class="form-control form-control-sm" dir="ltr" readonly value="{{ $medium->url() }}"></dd>
                    <dt class="col-4">{{ __('admin.fields.mime_type') }}</dt><dd class="col-8">{{ $medium->mime_type }}</dd>
                    <dt class="col-4">{{ __('admin.fields.dimensions') }}</dt><dd class="col-8">{{ $medium->width ? $medium->width.'×'.$medium->height : __('admin.none') }}</dd>
                    <dt class="col-4">{{ __('admin.fields.size') }}</dt><dd class="col-8">{{ number_format($medium->size / 1024) }} KB</dd>
                    <dt class="col-4">{{ __('admin.fields.uploaded_by') }}</dt><dd class="col-8">{{ $medium->uploader?->name ?? __('admin.none') }}</dd>
                </dl>
            </div></div>
        </div>
        <div class="col-lg-7">
            <form method="POST" action="{{ route('admin.media.update', $medium) }}" class="card" novalidate>
                <div class="card-body">
                    @csrf
                    @method('PUT')
                    <x-admin.form.input name="alt_text" :label="__('admin.fields.alt_text')" :value="$medium->alt_text" :help="__('admin.fields.alt_text_help')" />
                    <x-admin.form.input name="title" :label="__('admin.fields.title')" :value="$medium->title" />
                    <x-admin.form.textarea name="caption" :label="__('admin.fields.caption')" :value="$medium->caption" rows="2" />
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                        <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
