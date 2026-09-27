<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\TeamGroup;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_team_page_renders_with_one_h1_and_seo(): void
    {
        $html = $this->get('/team')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('<title>'.__('team.title').' | Paydar Group</title>', $html);
        $this->assertStringContainsString(__('team.empty'), $html);
        $this->assertStringContainsString('pg-header--light', $html);
        $this->assertContains('team', config('cms.reserved_slugs'));
    }

    public function test_active_groups_and_members_render_in_order_with_alternating_tones(): void
    {
        $founders = TeamGroup::factory()->create(['name' => 'بنیان‌گذار و شرکا', 'sort_order' => 1]);
        $exec = TeamGroup::factory()->create(['name' => 'تیم اجرایی', 'sort_order' => 2]);
        $hidden = TeamGroup::factory()->create(['name' => 'گروه مخفی', 'sort_order' => 3, 'is_active' => false]);
        $emptyGroup = TeamGroup::factory()->create(['name' => 'گروه خالی', 'sort_order' => 0]);
        $photo = Media::factory()->create(['alt_text' => 'عکس مدیر']);

        TeamMember::factory()->create(['team_group_id' => $founders->id, 'name' => 'مهدی مختاری ثابت', 'role' => 'مدیرعامل', 'photo_media_id' => $photo->id, 'linkedin_url' => 'https://linkedin.com/in/example', 'sort_order' => 1]);
        TeamMember::factory()->create(['team_group_id' => $founders->id, 'name' => 'عضو غیرفعال', 'is_active' => false]);
        TeamMember::factory()->create(['team_group_id' => $exec->id, 'name' => 'سهیل مطلبی', 'role' => 'مدیر اجرایی (COO)', 'photo_media_id' => null]);
        TeamMember::factory()->create(['team_group_id' => $hidden->id, 'name' => 'عضو گروه مخفی']);

        $html = $this->get('/team')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/pg-team__group--grey.*بنیان‌گذار و شرکا.*pg-team__group--warm.*تیم اجرایی/s', $html);
        $this->assertStringContainsString('مهدی مختاری ثابت', $html);
        $this->assertStringContainsString('alt="عکس مدیر"', $html);
        $this->assertStringContainsString('href="https://linkedin.com/in/example"', $html);
        $this->assertStringContainsString('aria-label="'.__('team.linkedin', ['name' => 'مهدی مختاری ثابت']).'"', $html);
        $this->assertStringNotContainsString('عضو غیرفعال', $html);
        $this->assertStringNotContainsString('گروه مخفی', $html);
        $this->assertStringNotContainsString('گروه خالی', $html);
        $this->assertStringNotContainsString(__('team.empty'), $html);
        $this->assertSame(2, substr_count($html, '<h2 id="team-group-'));
        $this->assertSame(2, substr_count($html, '<h3 class="pg-member__name">'));
    }
}
