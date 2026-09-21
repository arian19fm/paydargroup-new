<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Seo\Sitemap\SitemapBuilder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember(
            config('seo.sitemap_cache_key', 'seo.sitemap.xml'),
            now()->addSeconds((int) config('seo.sitemap_cache_seconds', 3600)),
            fn () => (new SitemapBuilder(config('seo.sitemap_sources', [])))->toXml()
        );

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
