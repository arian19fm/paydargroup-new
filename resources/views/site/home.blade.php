@extends('layouts.site')

{{--
    Home page. No page title is set, so <x-seo.head> renders the site name
    (plus tagline when configured). Other pages set their metadata in the
    controller, e.g. seo()->title('...')->description('...'), or at the top
    of the view inside an @php block — see docs/SEO.md.
--}}

@section('content')
    <x-layout.section>
        <h1>{{ \App\Support\Seo\SeoManager::siteName() }}</h1>
    </x-layout.section>
@endsection
