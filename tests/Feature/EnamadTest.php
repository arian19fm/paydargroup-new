<?php

namespace Tests\Feature;

use App\Support\Trust\Enamad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class EnamadTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    private const SNIPPET = "<a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=612345&Code=AbC123xYz'><img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=612345&Code=AbC123xYz' alt='' style='cursor:pointer' code='AbC123xYz'></a>";

    public function test_the_snippet_or_its_link_is_parsed_and_anything_else_rejected(): void
    {
        $this->assertSame(['id' => '612345', 'code' => 'AbC123xYz'], Enamad::parse(self::SNIPPET));
        $this->assertSame(['id' => '612345', 'code' => 'AbC123xYz'], Enamad::parse('https://trustseal.enamad.ir/?id=612345&amp;Code=AbC123xYz'));
        $this->assertNull(Enamad::parse('<a href="https://evil.example/?id=1&Code=x">x</a>'));
        $this->assertNull(Enamad::parse('https://trustseal.enamad.ir/?id=612345'));
        $this->assertNull(Enamad::parse(null));
    }

    public function test_admin_saves_the_code_and_the_footer_shows_the_seal(): void
    {
        $admin = $this->adminUser('admin');

        $this->assertStringNotContainsString('trustseal.enamad.ir', $this->get('/')->getContent(), 'no seal until configured');

        $this->actingAs($admin)->put('/admin/settings/general', ['values' => ['enamad_code' => '<script>alert(1)</script>']])
            ->assertSessionHasErrors('values.enamad_code');

        $this->actingAs($admin)->put('/admin/settings/general', ['values' => ['enamad_code' => self::SNIPPET.'<script>alert(1)</script>']])
            ->assertSessionHasNoErrors();
        $this->actingAs($admin)->get('/admin/settings/general')->assertOk()->assertSee(__('admin.settings.enamad_ok', ['id' => '612345']));

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('<a class="pg-footer__trust" href="https://trustseal.enamad.ir/?id=612345&amp;Code=AbC123xYz" target="_blank" rel="noopener" referrerpolicy="origin">', $html);
        $this->assertStringContainsString('src="https://trustseal.enamad.ir/logo.aspx?id=612345&amp;Code=AbC123xYz"', $html);
        $this->assertStringContainsString('code="AbC123xYz"', $html);
        $this->assertStringNotContainsString('alert(1)', $html);
    }
}
