<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_200(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_html_lang_and_direction_are_persian_rtl(): void
    {
        $this->get('/')
            ->assertSee('<html lang="fa" dir="rtl">', false);
    }

    public function test_home_page_renders_one_h1_and_landmarks(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('<header', $html);
        $this->assertStringContainsString('<main id="main"', $html);
        $this->assertStringContainsString('<footer', $html);
        $this->assertStringContainsString('class="pg-skip-link"', $html);
    }

    public function test_home_page_renders_default_seo_metadata(): void
    {
        $this->get('/')
            ->assertSee('<title>Paydar Group</title>', false)
            ->assertSee('<link rel="canonical" href="https://paydargroup.test">', false)
            ->assertSee('<meta property="og:url" content="https://paydargroup.test">', false)
            ->assertSee('<meta property="og:locale" content="fa_IR">', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertDontSee('"@type":"Article"', false)
            ->assertDontSee('"@type":"BreadcrumbList"', false);
    }

    public function test_home_page_is_noindex_outside_production(): void
    {
        config(['seo.indexable' => false]);

        $this->get('/')
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_home_page_is_indexable_when_enabled(): void
    {
        config(['seo.indexable' => true]);

        $this->get('/')
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_home_page_references_no_external_cdn(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertDoesNotMatchRegularExpression(
            '~(fonts\.googleapis\.com|fonts\.gstatic\.com|fonts\.bunny\.net|cdn\.jsdelivr\.net|unpkg\.com|cdnjs\.cloudflare\.com|ajax\.googleapis\.com|stackpath\.bootstrapcdn\.com)~i',
            $html
        );
    }
}
