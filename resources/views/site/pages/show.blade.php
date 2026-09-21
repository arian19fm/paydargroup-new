@extends('layouts.site')

{{--
    Structural page view (final design pending Figma). Content is stored as
    plain text/HTML-free until the editor policy is decided, so it is
    escaped and paragraphed here — see docs/CMS.md → "Editor strategy".
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
                {!! \App\Support\Content\PlainText::toHtml($page->content) !!}
            </div>
        </article>
    </x-layout.section>
@endsection
