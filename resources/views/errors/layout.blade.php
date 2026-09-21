@extends('layouts.site')

{{-- Shared structure for HTTP error pages. Error pages are never indexable. --}}
@php
    seo()->title(__('errors.'.$code.'.title'))->noindex();
@endphp

@section('content')
    <x-layout.section class="py-5 text-center">
        <p class="text-body-secondary mb-2">{{ $code }}</p>
        <h1>{{ __('errors.'.$code.'.title') }}</h1>
        <p class="lead">{{ __('errors.'.$code.'.message') }}</p>
        <a class="btn btn-primary mt-3" href="{{ route('home') }}">{{ __('errors.back_home') }}</a>
    </x-layout.section>
@endsection
