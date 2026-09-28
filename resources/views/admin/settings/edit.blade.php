@extends('layouts.admin')

@section('title', __('admin.nav.settings').': '.__($schema['label']))
@php $breadcrumbs = [['label' => __('admin.nav.settings')], ['label' => __($schema['label'])]]; @endphp

@section('content')
    <ul class="nav nav-tabs mb-3">
        @foreach ($groups as $key => $definition)
            <li class="nav-item"><a class="nav-link @if ($key === $group) active @endif" href="{{ route('admin.settings.edit', $key) }}" @if ($key === $group) aria-current="page" @endif>{{ __($definition['label']) }}</a></li>
        @endforeach
    </ul>

    <form method="POST" action="{{ route('admin.settings.update', $group) }}" class="card" novalidate>
        <div class="card-body" style="max-width: 44rem;">
            @csrf
            @method('PUT')
            @foreach ($schema['keys'] as $key => $definition)
                @php $name = "values[$key]"; $value = $values[$key] ?? null; $label = __($definition['label']); @endphp
                @if (! empty($definition['section']))
                    <h2 class="h6 fw-bold border-bottom pb-2 @if (! $loop->first) mt-4 @endif mb-3">{{ __($definition['section']) }}</h2>
                @endif
                @switch($definition['type'])
                    @case('text')
                        <x-admin.form.textarea :name="$name" :label="$label" :value="$value" rows="3" />
                        @break
                    @case('boolean')
                        <x-admin.form.checkbox :name="$name" :label="$label" :checked="(bool) $value" />
                        @break
                    @case('integer')
                        <x-admin.form.input :name="$name" type="number" min="0" :label="$label" :value="$value" />
                        @break
                    @case('media')
                        @php $medium = $value ? \App\Models\Media::find($value) : null; @endphp
                        <x-admin.form.input :name="$name" type="number" min="0" :label="$label" :value="$value" />
                        @if ($value && ! $medium)
                            <p class="form-text text-danger mt-n2 mb-3">{{ __('admin.settings.media_missing') }}</p>
                        @elseif ($medium)
                            <p class="form-text mt-n2 mb-3">
                                <a href="{{ route('admin.media.edit', $medium) }}">{{ $medium->original_filename }}</a>
                                <span class="text-body-secondary" dir="ltr">({{ $medium->mime_type }}@if ($medium->width), {{ $medium->width }}×{{ $medium->height }}@endif)</span>
                            </p>
                        @endif
                        @break
                    @case('url')
                        <x-admin.form.input :name="$name" type="url" :label="$label" :value="$value" dir="ltr" />
                        @break
                    @case('link')
                        <x-admin.form.input :name="$name" :label="$label" :value="$value" dir="ltr" :help="__('admin.settings.link_help')" />
                        @break
                    @case('email')
                        <x-admin.form.input :name="$name" type="email" :label="$label" :value="$value" dir="ltr" />
                        @break
                    @default
                        <x-admin.form.input :name="$name" :label="$label" :value="$value" />
                @endswitch
            @endforeach
            @can('settings.update')
                <button type="submit" class="btn btn-primary">{{ __('admin.save') }}</button>
            @endcan
        </div>
    </form>
@endsection
