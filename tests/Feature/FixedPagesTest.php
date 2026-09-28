<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The pages created by the ensure_fixed_pages migration, as a fresh server gets them. */
class FixedPagesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $keepImportedContent = true;

    public function test_fixed_pages_exist_and_about_is_the_designed_page(): void
    {
        $this->assertEqualsCanonicalizing(['about', 'privacy', 'terms'], Page::pluck('slug')->all());
        $this->assertFalse(Page::where('slug', 'privacy')->firstOrFail()->isPublished());
        $this->assertFalse(Page::where('slug', 'terms')->firstOrFail()->isPublished());

        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringContainsString('دربـاره پـایـدار گـروپ', $html);
        $this->assertStringContainsString('کانون ایران نوین', $html);
        $this->assertSame(5, substr_count(explode('pg-about__partners', $html)[0], '<p>') - substr_count(explode('pg-about__body', $html)[0], '<p>'));
        $this->assertSame(6, substr_count($html, '<li><img'));
        $this->assertStringContainsString('alt="Boltshift"', $html);
        $this->assertStringContainsString('/storage/media/designed/about-intro-1.png', $html);
        $this->assertStringContainsString('/storage/media/designed/about-history.png', $html);
        $this->assertStringContainsString('تاریخچه پایدار گروپ', $html);
        $this->assertStringContainsString('<dd>۲۵۷</dd>', $html);
        $this->assertStringContainsString('<dd>۵</dd>', $html);

        $this->get('/privacy')->assertNotFound();
        $this->get('/terms')->assertNotFound();
    }
}
