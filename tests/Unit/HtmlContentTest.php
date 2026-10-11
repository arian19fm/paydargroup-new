<?php

namespace Tests\Unit;

use App\Support\Content\HtmlContent;
use Tests\TestCase;

class HtmlContentTest extends TestCase
{
    public function test_sanitize_strips_scripts_handlers_and_unsafe_urls(): void
    {
        $clean = HtmlContent::sanitize(
            '<p onclick="x()" style="color:red" class="lead">متن <a href="javascript:alert(1)">بد</a> <a href="https://example.com">خوب</a></p>'
            .'<script>alert(1)</script><iframe src="https://example.com"></iframe><img src="data:image/png;base64,AA" onerror="x()" alt="a">'
        );

        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('style', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('<iframe', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('data:', $clean);
        $this->assertStringContainsString('<p class="lead">', $clean);
        $this->assertStringContainsString('<a href="https://example.com">خوب</a>', $clean);
    }

    public function test_sanitize_keeps_seo_markup_and_unwraps_layout_elements(): void
    {
        $clean = HtmlContent::sanitize('<article><h2 id="intro">مقدمه</h2><figure><img src="/storage/a.webp" alt="نمودار" width="800" height="450"><figcaption>شرح</figcaption></figure><table><caption>جدول</caption><tr><th scope="col">الف</th></tr></table></article>');

        $this->assertStringNotContainsString('<article', $clean);
        $this->assertStringContainsString('<h2 id="intro">مقدمه</h2>', $clean);
        $this->assertStringContainsString('alt="نمودار" width="800" height="450"', $clean);
        $this->assertStringContainsString('<figcaption>شرح</figcaption>', $clean);
        $this->assertStringContainsString('<th scope="col">الف</th>', $clean);
    }

    public function test_empty_markup_counts_as_empty(): void
    {
        $this->assertSame('', HtmlContent::sanitize('<p></p><p>&nbsp;</p>'));
        $this->assertSame('', HtmlContent::sanitize(null));
        $this->assertNotSame('', HtmlContent::sanitize('<p></p><img src="/a.png" alt="">'));
    }

    public function test_render_shifts_headings_and_builds_the_table_of_contents(): void
    {
        $body = HtmlContent::render('<h1>یک</h1><p>متن</p><h2>دو</h2><h1 id="custom">سه</h1>', topHeading: 2);

        $this->assertSame('<h2 id="section-1">یک</h2><p>متن</p><h3>دو</h3><h2 id="custom">سه</h2>', $body['html']);
        $this->assertSame([['id' => 'section-1', 'text' => 'یک'], ['id' => 'custom', 'text' => 'سه']], $body['toc']);

        $this->assertSame('<h3>عنوان</h3><h4>زیر</h4>', HtmlContent::toHtml('<h2>عنوان</h2><h3>زیر</h3>', topHeading: 3), 'toHtml() adds no anchors');
        $this->assertSame('<h4>عمیق</h4>', HtmlContent::toHtml('<h4>عمیق</h4>', topHeading: 2), 'deeper headings are never raised');
    }

    public function test_render_enhances_links_images_and_tables(): void
    {
        $html = HtmlContent::toHtml('<p><a href="https://example.com" target="_blank">لینک</a></p><img src="/a.png" alt="x"><table><tr><td>۱</td></tr></table>');

        $this->assertStringContainsString('target="_blank" rel="noopener"', $html);
        $this->assertStringContainsString('loading="lazy" decoding="async"', $html);
        $this->assertStringContainsString('<div class="pg-content__table"><table>', $html);
    }

    public function test_plain_text_is_rendered_as_escaped_paragraphs(): void
    {
        $this->assertSame("<p>خط ۱<br>\nخط ۲ &lt; ۳</p>\n<p>بند دوم</p>", HtmlContent::toHtml("خط ۱\nخط ۲ < ۳\n\nبند دوم"));
        $this->assertSame(['html' => '', 'toc' => []], HtmlContent::render('  '));
    }

    public function test_pasted_html_source_is_used_as_markup(): void
    {
        $expected = '<h2>تیتر</h2><p>متن <strong>مهم</strong></p>';
        $strip = fn (string $html) => preg_replace('/>\s+</', '><', HtmlContent::toHtml($html));

        // Pasted into the visual editor (escaped, one paragraph per line), in a ```html fence.
        $this->assertSame($expected, $strip('<p>```html</p><p>&lt;h2&gt;تیتر&lt;/h2&gt;</p><p></p><p>&lt;p&gt;متن &lt;strong&gt;مهم&lt;/strong&gt;&lt;/p&gt;</p><p>```</p>'));
        // Pasted into a code block.
        $this->assertSame($expected, $strip('<pre><code>&lt;h2&gt;تیتر&lt;/h2&gt;&lt;p&gt;متن &lt;strong&gt;مهم&lt;/strong&gt;&lt;/p&gt;</code></pre>'));
        // Fenced source in the HTML tab; a whole document.
        $this->assertSame($expected, $strip("```html\n<h2>تیتر</h2>\n<p>متن <strong>مهم</strong></p>\n```"));
        $this->assertSame($expected, $strip('<!DOCTYPE html><html lang="fa"><head><title>x</title><style>p{color:red}</style></head><body><h2>تیتر</h2><p>متن <strong>مهم</strong></p></body></html>'));

        // Content that only quotes some markup stays as written.
        $this->assertSame('<p>تگ &lt;p&gt; یعنی پاراگراف</p>', HtmlContent::toHtml('<p>تگ &lt;p&gt; یعنی پاراگراف</p>'));
        $this->assertSame('<h2>نمونه</h2><pre><code>&lt;p&gt;x&lt;/p&gt;</code></pre>', HtmlContent::toHtml('<h2>نمونه</h2><pre><code>&lt;p&gt;x&lt;/p&gt;</code></pre>'));
    }
}
