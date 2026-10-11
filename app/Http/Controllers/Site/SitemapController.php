<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Seo\Sitemap\Sitemap;
use Illuminate\Http\Response;

/** The dynamic sitemap: index, one file per section, and the news sitemap (App\Support\Seo\Sitemap\Sitemap). */
class SitemapController extends Controller
{
    public function __construct(protected Sitemap $sitemap) {}

    public function index(): Response
    {
        return $this->xml($this->sitemap->indexXml());
    }

    public function section(string $name): Response
    {
        abort_unless($this->sitemap->has($name), 404);

        return $this->xml($this->sitemap->sectionXml($name));
    }

    public function news(): Response
    {
        return $this->xml($this->sitemap->newsXml());
    }

    protected function xml(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
