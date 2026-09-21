{{--
    SEO <head> block. Reads the request-scoped SeoManager (seo() helper);
    pages set values in their controller or at the top of their view.
    Every tag has a default so the head is always complete and valid.
--}}
@php
    $seo = seo();
    $title = $seo->fullTitle();
    $description = $seo->getDescription();
    $canonical = $seo->getCanonical();
    $image = $seo->getImage();
    $ogTitle = $seo->rawTitle() ?? $title;
@endphp
<title>{{ $title }}</title>
@if ($description)
<meta name="description" content="{{ $description }}">
@endif
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $seo->robots() }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $seo->getOgType() }}">
<meta property="og:site_name" content="{{ config('site.name') }}">
<meta property="og:locale" content="{{ config('site.og_locale') }}">
<meta property="og:title" content="{{ $ogTitle }}">
@if ($description)
<meta property="og:description" content="{{ $description }}">
@endif
<meta property="og:url" content="{{ $canonical }}">
@if ($image)
<meta property="og:image" content="{{ $image['url'] }}">
@isset($image['width'])
<meta property="og:image:width" content="{{ $image['width'] }}">
@endisset
@isset($image['height'])
<meta property="og:image:height" content="{{ $image['height'] }}">
@endisset
@isset($image['alt'])
<meta property="og:image:alt" content="{{ $image['alt'] }}">
@endisset
@endif

{{-- Twitter / X --}}
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
@if (config('seo.twitter_site'))
<meta name="twitter:site" content="{{ config('seo.twitter_site') }}">
@endif
<meta name="twitter:title" content="{{ $ogTitle }}">
@if ($description)
<meta name="twitter:description" content="{{ $description }}">
@endif
@if ($image)
<meta name="twitter:image" content="{{ $image['url'] }}">
@isset($image['alt'])
<meta name="twitter:image:alt" content="{{ $image['alt'] }}">
@endisset
@endif

{{-- Alternate languages (only when a page provides them) --}}
@foreach ($seo->getAlternates() as $hreflang => $url)
<link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $url }}">
@endforeach

{{-- Icons (self-hosted, see config/site.php) --}}
@foreach (config('site.icons', []) as $icon)
<link {!! collect($icon)->map(fn ($v, $k) => e($k).'="'.e($v).'"')->implode(' ') !!}>
@endforeach

{{-- Structured data --}}
@foreach ($seo->jsonLdObjects() as $object)
<x-seo.json-ld :data="$object" />
@endforeach
