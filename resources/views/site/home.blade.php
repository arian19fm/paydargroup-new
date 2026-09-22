@extends('layouts.site')

{{--
    Home page (Figma 110:262 desktop / 172:289 mobile). No page title is
    set, so <x-seo.head> renders the site name (plus tagline when
    configured). The hero is yielded separately so the layout can float the
    glass header over it; the remaining sections follow the Figma order.
--}}

@section('body_class', 'pg-page-home')

@push('head')
    @if ($heroImage ?? null)
        <link rel="preload" as="image" href="{{ $heroImage->url() }}" fetchpriority="high">
    @else
        <link rel="preload" as="image" href="{{ asset('images/home/hero.webp') }}" type="image/webp" fetchpriority="high">
    @endif
    {{-- Flags a scripted page so the hero load-in keyframes run (without JS the hero is simply static). --}}
    <script>document.documentElement.classList.add('pg-js');</script>
@endpush

@section('hero')
    @include('partials.site.home.hero')
@endsection

@section('content')
    @include('partials.site.home.products')
    @include('partials.site.home.blog')
    @include('partials.site.home.faq')
    @include('partials.site.home.contact')
@endsection
