@extends('layouts.admin')

@php $editing = $redirect->exists; @endphp
@section('title', $editing ? __('admin.edit').': '.$redirect->source_path : __('admin.create'))
@php $breadcrumbs = [['label' => __('admin.nav.redirects'), 'url' => route('admin.redirects.index')], ['label' => $editing ? $redirect->source_path : __('admin.create')]]; @endphp

@section('content')
    <form method="POST" action="{{ $editing ? route('admin.redirects.update', $redirect) : route('admin.redirects.store') }}" class="card" novalidate>
        <div class="card-body" style="max-width: 40rem;">
            @csrf
            @if ($editing) @method('PUT') @endif
            <x-admin.form.input name="source_path" :label="__('admin.fields.source_path')" :value="$redirect->source_path" :help="__('admin.fields.source_path_help')" dir="ltr" required />
            <x-admin.form.input name="destination_url" :label="__('admin.fields.destination_url')" :value="$redirect->destination_url" :help="__('admin.fields.destination_url_help')" dir="ltr" required />
            <x-admin.form.select name="http_status" :label="__('admin.fields.http_status')" :options="array_combine(App\Models\Redirect::STATUS_CODES, App\Models\Redirect::STATUS_CODES)" :selected="$redirect->http_status ?? 301" required />
            <x-admin.form.checkbox name="is_active" :label="__('admin.fields.is_active')" :checked="$redirect->is_active ?? true" />
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
                <a href="{{ route('admin.redirects.index') }}" class="btn btn-outline-secondary">{{ __('admin.cancel') }}</a>
            </div>
        </div>
    </form>
@endsection
