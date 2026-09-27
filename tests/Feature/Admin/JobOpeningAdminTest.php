<?php

namespace Tests\Feature\Admin;

use App\Models\JobOpening;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class JobOpeningAdminTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_editor_manages_openings(): void
    {
        $editor = $this->adminUser('editor');

        $this->actingAs($editor)->get('/admin/jobs')->assertOk();
        $this->actingAs($editor)->get('/admin/jobs/create')->assertOk();
        $this->actingAs($editor)->post('/admin/jobs', [
            'title' => 'طراح محصول دیجیتال', 'category' => 'طراحی محصول', 'tone' => 'blue', 'description' => 'توضیح',
            'employment_type' => 'تمام‌وقت', 'location' => 'تهران / حضوری', 'apply_url' => ' hr@example.com ', 'sort_order' => 2, 'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/jobs');

        $job = JobOpening::firstOrFail();
        $this->assertSame('hr@example.com', $job->apply_url);
        $this->assertSame('mailto:hr@example.com', $job->applyHref());
        $this->assertSame($editor->id, $job->created_by);

        $this->actingAs($editor)->put("/admin/jobs/{$job->id}", [
            'title' => 'طراح محصول', 'tone' => 'green', 'apply_url' => 'https://example.com/apply', 'sort_order' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $job->refresh();
        $this->assertSame('green', $job->tone);
        $this->assertSame('https://example.com/apply', $job->applyHref());
        $this->assertFalse($job->is_active);

        $this->actingAs($editor)->get('/admin/jobs?q=طراح')->assertOk()->assertSee('طراح محصول');
        $this->actingAs($editor)->delete("/admin/jobs/{$job->id}")->assertRedirect('/admin/jobs');
        $this->assertDatabaseMissing('job_openings', ['id' => $job->id]);
    }

    public function test_validation_rejects_bad_tone_and_apply_target(): void
    {
        $admin = $this->adminUser('admin');

        $this->actingAs($admin)->post('/admin/jobs', ['title' => 'x', 'tone' => 'pink', 'apply_url' => 'not-a-link'])
            ->assertSessionHasErrors(['tone', 'apply_url']);
        $this->actingAs($admin)->post('/admin/jobs', ['title' => 'x', 'tone' => 'red', 'apply_url' => 'ftp://example.com'])
            ->assertSessionHasErrors(['apply_url']);

        auth()->logout();
        $this->get('/admin/jobs')->assertRedirect('/admin/login');
    }
}
