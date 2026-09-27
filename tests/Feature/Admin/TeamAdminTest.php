<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Models\TeamGroup;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class TeamAdminTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_admin_manages_groups_and_members(): void
    {
        $admin = $this->adminUser('admin');

        $this->actingAs($admin)->get('/admin/team/groups')->assertOk();
        $this->actingAs($admin)->post('/admin/team/groups', ['name' => 'تیم فنی', 'sort_order' => 4, 'is_active' => 1])->assertRedirect('/admin/team/groups');
        $group = TeamGroup::firstOrFail();

        $this->actingAs($admin)->get('/admin/team/members/create')->assertOk()->assertSee('تیم فنی');
        $this->actingAs($admin)->post('/admin/team/members', [
            'team_group_id' => $group->id, 'name' => 'آرین فغانی مقدم', 'role' => 'مدیر فنی',
            'linkedin_url' => 'https://linkedin.com/in/arian', 'photo_media_id' => '', 'sort_order' => 2, 'is_active' => 1,
        ])->assertRedirect('/admin/team/members')->assertSessionHasNoErrors();

        $member = TeamMember::firstOrFail();
        $this->assertSame('مدیر فنی', $member->role);
        $this->assertNull($member->photo_media_id);

        $this->actingAs($admin)->put("/admin/team/members/{$member->id}", [
            'team_group_id' => $group->id, 'name' => 'آرین فغانی مقدم', 'role' => 'CTO', 'linkedin_url' => '', 'sort_order' => 1, 'is_active' => 0,
        ])->assertRedirect();
        $member->refresh();
        $this->assertSame('CTO', $member->role);
        $this->assertNull($member->linkedin_url);
        $this->assertFalse($member->is_active);

        $this->actingAs($admin)->get('/admin/team/members?q=فغانی')->assertOk()->assertSee('آرین فغانی مقدم');

        $this->actingAs($admin)->delete("/admin/team/groups/{$group->id}")->assertRedirect();
        $this->assertDatabaseMissing('team_members', ['id' => $member->id]); // members go with their group
    }

    public function test_member_photo_is_uploaded_from_the_form_and_can_be_removed(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser('admin');
        $group = TeamGroup::factory()->create();

        $this->actingAs($admin)->post('/admin/team/members', [
            'team_group_id' => $group->id, 'name' => 'زینب امیدی', 'role' => 'طراح گرافیک', 'is_active' => 1,
            'photo' => UploadedFile::fake()->image('zeinab.png', 600, 640),
        ])->assertRedirect('/admin/team/members')->assertSessionHasNoErrors();

        $member = TeamMember::firstOrFail();
        $photo = Media::findOrFail($member->photo_media_id);
        $this->assertSame('زینب امیدی', $photo->title);
        $this->assertSame('زینب امیدی', $photo->alt_text);
        Storage::disk('public')->assertExists($photo->path);

        $this->actingAs($admin)->get('/admin/team/members')->assertOk()->assertSee($photo->url());
        $this->actingAs($admin)->get("/admin/team/members/{$member->id}/edit")->assertOk()->assertSee('remove_photo');

        $this->actingAs($admin)->post('/admin/team/members', [
            'team_group_id' => $group->id, 'name' => 'x', 'photo' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('photo');

        $this->actingAs($admin)->put("/admin/team/members/{$member->id}", [
            'team_group_id' => $group->id, 'name' => 'زینب امیدی', 'photo_media_id' => $photo->id, 'remove_photo' => 1, 'is_active' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($member->fresh()->photo_media_id);
    }

    public function test_member_validation_rejects_bad_group_photo_and_url(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post('/admin/team/members', [
            'team_group_id' => 999, 'name' => '', 'linkedin_url' => 'not-a-url', 'photo_media_id' => 424242,
        ])->assertSessionHasErrors(['team_group_id', 'name', 'linkedin_url', 'photo_media_id']);
    }

    public function test_editors_can_view_but_not_change_the_team(): void
    {
        $editor = $this->editor();
        $group = TeamGroup::factory()->create();

        $this->actingAs($editor)->get('/admin/team/members')->assertOk()->assertDontSee('/admin/team/members/create');
        $this->actingAs($editor)->post('/admin/team/members', ['team_group_id' => $group->id, 'name' => 'x'])->assertForbidden();
        $this->actingAs($editor)->post('/admin/team/groups', ['name' => 'x'])->assertForbidden();
    }
}
