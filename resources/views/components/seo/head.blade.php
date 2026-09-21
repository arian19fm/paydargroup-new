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
    $ogTitle = $seo->getOgTitle();
    $ogDescription = $seo->getOgDescription();
    $twitterTitle = $seo->getTwitterTitle();
    $twitterDescription = $seo->getTwitterDescription();
    $twitterImage = $seo->getTwitterImage();
    $twitterSite = $seo->getTwitterSite();
@endphp
<title>{{ $title }}</title>
@if ($description)
<meta name="description" content="{{ $description }}">
@endif
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $seo->robots() }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $seo->getOgType() }}">
<meta property="og:site_name" content="{{ \App\Support\Seo\SeoManager::siteName() }}">
<meta property="og:locale" content="{{ config('site.og_locale') }}">
<meta property="og:title" content="{{ $ogTitle }}">
@if ($ogDescription)
<meta property="og:description" content="{{ $ogDescription }}">
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
<meta name="twitter:card" content="{{ $twitterImage ? 'summary_large_image' : 'summary' }}">
@if ($twitterSite)
<meta name="twitter:site" content="{{ $twitterSite }}">
@endif
<meta name="twitter:title" content="{{ $twitterTitle }}">
@if ($twitterDescription)
<meta name="twitter:description" content="{{ $twitterDescription }}">
@endif
@if ($twitterImage)
<meta name="twitter:image" content="{{ $twitterImage['url'] }}">
@isset($twitterImage['alt'])
<meta name="twitter:image:alt" content="{{ $twitterImage['alt'] }}">
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
