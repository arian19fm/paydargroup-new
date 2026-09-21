<?php

namespace Tests\Unit;

use App\Support\Seo\SeoManager;
use Tests\TestCase;

class SeoManagerTest extends TestCase
{
    public function test_full_title_uses_site_name_alone_on_pages_without_a_title(): void
    {
        $seo = new SeoManager;

        $this->assertSame('Paydar Group', $seo->fullTitle());

        config(['site.tagline' => 'شعار']);
        $this->assertSame('Paydar Group | شعار', $seo->fullTitle());
    }

    public function test_full_title_appends_site_name_to_page_title(): void
    {
        $seo = (new SeoManager)->title('  تماس با ما ');

        $this->assertSame('تماس با ما', $seo->rawTitle());
        $this->assertSame('تماس با ما | Paydar Group', $seo->fullTitle());
    }

    public function test_description_is_normalised_and_limited(): void
    {
        $seo = (new SeoManager)->description("<p>سلام</p>\n\n   دنیا ".str_repeat('x', 400));

        $this->assertStringStartsWith('سلام دنیا ', $seo->getDescription());
        $this->assertLessThanOrEqual(300, mb_strlen($seo->getDescription()));
    }

    public function test_absolute_url_is_built_from_app_url(): void
    {
        $this->assertSame('https://paydargroup.test', SeoManager::absoluteUrl('/'));
        $this->assertSame('https://paydargroup.test/news/slug', SeoManager::absoluteUrl('/news/slug/'));
    }

    public function test_article_schema_is_only_added_when_requested(): void
    {
        $seo = new SeoManager;
        $types = array_column($seo->jsonLdObjects(), '@type');

        $this->assertSame(['Organization', 'WebSite'], $types);

        $seo->jsonLd(['@type' => 'Article', 'headline' => 'x']);
        $this->assertContains('Article', array_column($seo->jsonLdObjects(), '@type'));
    }
}
