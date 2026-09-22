<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Page;
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

    public function test_home_page_renders_every_designed_section_in_order(): void
    {
        $html = $this->get('/')->getContent();

        $positions = array_map(fn (string $needle) => strpos($html, $needle), [
            'class="pg-header',
            'class="pg-hero"',
            'id="products"',
            'id="blog"',
            'id="faq"',
            'id="contact"',
            'class="pg-footer',
        ]);

        $this->assertNotContains(false, $positions);
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions, 'Sections must follow the Figma order.');
    }

    public function test_hero_headline_is_the_single_h1(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertMatchesRegularExpression('/<h1[^>]*class="pg-hero__title"/', $html);
        $this->assertStringContainsString(__('home.hero.headline')[0], $html);
    }

    public function test_products_section_lists_the_five_products(): void
    {
        $html = $this->get('/')->getContent();

        foreach (config('home.products') as $product) {
            $this->assertStringContainsString(__('home.products.items.'.$product['key'].'.name'), $html);
            $this->assertStringContainsString('/images/home/'.$product['image'].'.webp', $html);
        }

        $this->assertSame(5, substr_count($html, 'class="pg-product '));
    }

    public function test_published_articles_appear_and_drafts_do_not(): void
    {
        $published = Article::factory()->published()->create(['title' => 'مقاله منتشرشده']);
        $draft = Article::factory()->create(['title' => 'پیش‌نویس مخفی']);
        $scheduled = Article::factory()->scheduled()->create(['title' => 'مقاله زمان‌بندی‌شده']);

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString($published->title, $html);
        $this->assertStringContainsString(route('articles.show', $published->slug), $html);
        $this->assertStringNotContainsString($draft->title, $html);
        $this->assertStringNotContainsString($scheduled->title, $html);
        $this->assertStringNotContainsString(__('home.blog.empty'), $html);
    }

    public function test_blog_section_shows_an_empty_state_without_articles(): void
    {
        $this->get('/')
            ->assertSee('id="blog"', false)
            ->assertSee(__('home.blog.empty'));
    }

    public function test_calls_to_action_link_only_to_published_pages(): void
    {
        $html = $this->get('/')->getContent();
        $this->assertStringNotContainsString(route('pages.show', 'about'), $html);

        Page::factory()->published()->create(['slug' => 'about']);

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('href="'.route('pages.show', 'about').'"', $html);
    }

    public function test_home_page_has_no_temporary_figma_or_cdn_asset_urls(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('figma.com', $html);
        // asset() builds on the request host (localhost under test); anything
        // else is an external dependency.
        $this->assertDoesNotMatchRegularExpression(
            '~https?://(?!paydargroup\.test|localhost)[^"\s]*\.(css|js|woff2?|png|jpe?g|webp|svg)~i',
            $html,
            'Every asset must be served from this site.'
        );
    }

    public function test_faq_is_an_accessible_accordion_with_first_item_open(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('id="faq-panel-1" class="collapse show"', $html);
        $this->assertStringContainsString('aria-expanded="true" aria-controls="faq-panel-1"', $html);
        $this->assertStringContainsString('aria-expanded="false" aria-controls="faq-panel-2"', $html);
        $this->assertSame(count(__('home.faq.items')), substr_count($html, 'data-bs-toggle="collapse"'));
    }

    public function test_mobile_navigation_offcanvas_is_present_and_labelled(): void
    {
        $this->get('/')
            ->assertSee('data-bs-toggle="offcanvas" data-bs-target="#site-nav"', false)
            ->assertSee('id="site-nav" aria-labelledby="site-nav-label"', false)
            ->assertSee('/images/icons/menu-01.svg', false);
    }
}
