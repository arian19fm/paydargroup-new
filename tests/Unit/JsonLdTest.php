<?php

namespace Tests\Unit;

use App\Support\Seo\JsonLd;
use PHPUnit\Framework\TestCase;

class JsonLdTest extends TestCase
{
    public function test_encoding_cannot_break_out_of_the_script_tag(): void
    {
        $json = JsonLd::encode(['name' => '</script><script>alert(1)</script>', 'note' => 'a & b']);

        $this->assertStringNotContainsString('</script>', $json);
        $this->assertStringNotContainsString('<', $json);
        $this->assertStringContainsString('\\u003C/script\\u003E', $json);
        $this->assertStringContainsString('\\u0026', $json);
    }

    public function test_encoding_keeps_unicode_and_slashes_readable(): void
    {
        $json = JsonLd::encode(['name' => 'گروه پایدار', 'url' => 'https://example.test/a/b']);

        $this->assertStringContainsString('"گروه پایدار"', $json);
        $this->assertStringContainsString('https://example.test/a/b', $json);
    }

    public function test_clean_removes_empty_values_recursively(): void
    {
        $cleaned = JsonLd::clean([
            'a' => 'x',
            'b' => null,
            'c' => '',
            'd' => [],
            'e' => ['f' => null, 'g' => 'y', 'h' => ['i' => null]],
        ]);

        $this->assertSame(['a' => 'x', 'e' => ['g' => 'y']], $cleaned);
    }

    public function test_breadcrumb_list_positions_items_and_omits_missing_urls(): void
    {
        $schema = JsonLd::breadcrumbList([
            ['label' => 'خانه', 'url' => 'https://example.test'],
            ['label' => 'فعلی', 'url' => null],
        ]);

        $this->assertSame('BreadcrumbList', $schema['@type']);
        $this->assertSame(1, $schema['itemListElement'][0]['position']);
        $this->assertSame('https://example.test', $schema['itemListElement'][0]['item']);
        $this->assertSame(2, $schema['itemListElement'][1]['position']);
        $this->assertArrayNotHasKey('item', $schema['itemListElement'][1]);
    }
}
