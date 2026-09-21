<?php

namespace Tests\Feature;

use Tests\TestCase;

class RobotsAndSitemapTest extends TestCase
{
    public function test_robots_txt_disallows_everything_when_not_indexable(): void
    {
        config(['seo.indexable' => false]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee("User-agent: *\nDisallow: /\n", false)
            ->assertDontSee('Sitemap:');
    }

    public function test_robots_txt_allows_crawling_in_production_mode(): void
    {
        config(['seo.indexable' => true]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee("Allow: /\n", false)
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: https://paydargroup.test/sitemap.xml')
            ->assertDontSee('Disallow: /build');
    }

    public function test_sitemap_lists_static_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
            ->assertSee('<loc>https://paydargroup.test</loc>', false);
    }
}
