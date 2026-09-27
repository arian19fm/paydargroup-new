<?php

namespace Tests\Unit;

use App\Support\Content\JobBody;
use Tests\TestCase;

class JobBodyTest extends TestCase
{
    public function test_headings_bullets_and_paragraphs_are_rendered_and_escaped(): void
    {
        $html = JobBody::toHtml("# شرح موقعیت\nخط اول\nخط دوم <b>x</b>\n\n- مورد یک\n• مورد دو\n\n## مهارت‌ها\n* مورد سه\nپایان");

        $this->assertSame(
            "<h3>شرح موقعیت</h3>\n<p>خط اول<br>خط دوم &lt;b&gt;x&lt;/b&gt;</p>\n<ul><li>مورد یک</li><li>مورد دو</li></ul>\n<h3>مهارت‌ها</h3>\n<ul><li>مورد سه</li></ul>\n<p>پایان</p>",
            $html
        );
        $this->assertSame('', JobBody::toHtml("  \n"));
        $this->assertSame('', JobBody::toHtml(null));
    }
}
