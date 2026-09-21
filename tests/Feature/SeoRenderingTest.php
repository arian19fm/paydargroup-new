<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SeoRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // A throwaway page that sets page-specific metadata the way a
        // controller would, then renders the public layout.
        Route::middleware('web')->get('/_test/seo-sample', function () {
            seo()
                ->title('درباره ما')
                ->description("  توضیح   <b>صفحه</b>\n نمونه ")
                ->image('https://paydargroup.test/img/og.png', 1200, 630, 'تصویر')
                ->breadcrumbs([
                    ['label' => 'خانه', 'url' => 'https://paydargroup.test'],
                    ['label' => 'درباره ما'],
                ])
                ->alternates(['fa' => 'https://paydargroup.test/_test/seo-sample', 'en' => 'https://paydargroup.test/en/about']);

            return view('layouts.site');
        });
    }

    public function test_page_specific_metadata_is_rendered(): void
    {
        $this->get('/_test/seo-sample?utm_source=x')
            ->assertOk()
            ->assertSee('<title>درباره ما | Paydar Group</title>', false)
            ->assertSee('<meta name="description" content="توضیح صفحه نمونه">', false)
            ->assertSee('<link rel="canonical" href="https://paydargroup.test/_test/seo-sample">', false)
            ->assertSee('<meta property="og:title" content="درباره ما">', false)
            ->assertSee('<meta property="og:image" content="https://paydargroup.test/img/og.png">', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<link rel="alternate" hreflang="en" href="https://paydargroup.test/en/about">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="https://paydargroup.test/_test/seo-sample">', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"position":2,"name":"درباره ما"', false);
    }

    public function test_canonical_can_preserve_pagination_query(): void
    {
        Route::middleware('web')->get('/_test/seo-paged', function () {
            seo()->canonicalQuery(['page']);

            return view('layouts.site');
        });

        $this->get('/_test/seo-paged?page=3&sort=x')
            ->assertSee('<link rel="canonical" href="https://paydargroup.test/_test/seo-paged?page=3">', false);

        // page=1 is the same document as the unpaginated URL.
        $this->get('/_test/seo-paged?page=1')
            ->assertSee('<link rel="canonical" href="https://paydargroup.test/_test/seo-paged">', false);
    }

    public function test_explicit_canonical_override_wins(): void
    {
        Route::middleware('web')->get('/_test/seo-override', function () {
            seo()->canonical('https://paydargroup.test/preferred');

            return view('layouts.site');
        });

        $this->get('/_test/seo-override')
            ->assertSee('<link rel="canonical" href="https://paydargroup.test/preferred">', false);
    }

    public function test_breadcrumb_component_matches_seo_breadcrumbs(): void
    {
        Route::middleware('web')->get('/_test/seo-crumbs', function () {
            seo()->breadcrumbs([
                ['label' => 'خانه', 'url' => 'https://paydargroup.test'],
                ['label' => 'اخبار', 'url' => 'https://paydargroup.test/news'],
                ['label' => 'عنوان خبر'],
            ]);

            return view('components.ui.breadcrumb');
        });

        $this->get('/_test/seo-crumbs')
            ->assertSee('<nav aria-label="مسیر صفحه"', false)
            ->assertSee('<a href="https://paydargroup.test/news">اخبار</a>', false)
            ->assertSee('<li class="breadcrumb-item active" aria-current="page">عنوان خبر</li>', false);
    }
}
