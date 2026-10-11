@extends('layouts.site')

{{--
    Structural page view (final design pending Figma). The body is the
    editor's sanitised HTML — see docs/CMS.md → "Editor strategy".
--}}
@section('content')
    <x-layout.section class="py-4">
        <x-ui.breadcrumb class="mb-3" />
        <article>
            <h1>{{ $page->title }}</h1>
            @if ($page->excerpt)
                <p class="lead">{{ $page->excerpt }}</p>
            @endif
            <div class="pg-content">
                {!! \App\Support\Content\HtmlContent::toHtml($page->content) !!}
            </div>
        </article>
    </x-layout.section>
@endsection
