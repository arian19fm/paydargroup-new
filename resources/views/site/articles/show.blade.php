@extends('layouts.site')

@section('content')
    <x-layout.section class="py-4">
        <x-ui.breadcrumb class="mb-3" />
        <article>
            <header class="mb-4">
                <h1>{{ $article->title }}</h1>
                <p class="text-body-secondary small">
                    <time datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->published_at->translatedFormat('j F Y') }}</time>
                    @if ($article->author)
                        · {{ $article->author->name }}
                    @endif
                    @if ($article->categories->isNotEmpty())
                        · {{ $article->categories->pluck('name')->join('، ') }}
                    @endif
                </p>
                @if ($article->featuredImage)
                    <img src="{{ $article->featuredImage->url() }}" alt="{{ $article->featuredImage->alt_text }}"
                         width="{{ $article->featuredImage->width }}" height="{{ $article->featuredImage->height }}"
                         class="img-fluid rounded" decoding="async">
                @endif
            </header>
            @if ($article->excerpt)
                <p class="lead">{{ $article->excerpt }}</p>
            @endif
            <div class="pg-content">
                {!! \App\Support\Content\PlainText::toHtml($article->content) !!}
            </div>
        </article>
    </x-layout.section>
@endsection
