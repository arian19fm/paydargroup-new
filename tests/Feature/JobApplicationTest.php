<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_careers_page_renders_detail_and_form_modals_for_each_opening(): void
    {
        $job = JobOpening::factory()->create([
            'title' => 'مدیر فروش استراتژیک', 'apply_url' => null, 'body' => "# شرح موقعیت\n- مورد یک",
            'specs' => [['label' => 'سطح ارشدیت', 'value' => 'کارشناس ارشد']],
        ]);
        $external = JobOpening::factory()->create(['title' => 'خارجی', 'apply_url' => 'https://example.com/apply']);

        $html = $this->get('/careers')->assertOk()->getContent();

        $this->assertStringContainsString('href="'.route('careers.show', $job).'" data-bs-toggle="modal" data-bs-target="#job-modal-'.$job->id.'"', $html);
        $this->assertStringContainsString('id="job-modal-'.$job->id.'"', $html);
        $this->assertStringContainsString('id="job-apply-'.$job->id.'"', $html);
        $this->assertStringContainsString('<h3>شرح موقعیت</h3>', $html);
        $this->assertStringContainsString('<li>مورد یک</li>', $html);
        $this->assertStringContainsString('<dt>سطح ارشدیت</dt>', $html);
        $this->assertStringContainsString('action="'.route('careers.apply', $job).'"', $html);
        $this->assertStringContainsString('data-bs-target="#job-apply-'.$job->id.'"', $html);
        $this->assertStringNotContainsString('id="job-modal-'.$external->id.'"', $html); // external link: no modal
        $this->assertStringContainsString('href="https://example.com/apply"', $html);
    }

    public function test_opening_page_serves_the_same_detail_and_form_without_javascript(): void
    {
        $job = JobOpening::factory()->create(['title' => 'طراح محصول', 'description' => 'توضیح کوتاه', 'body' => null]);
        $inactive = JobOpening::factory()->create(['is_active' => false]);

        $html = $this->get('/careers/'.$job->id)->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('<title>طراح محصول | '.__('careers.title').' | Paydar Group</title>', $html);
        $this->assertStringContainsString('<p>توضیح کوتاه</p>', $html);
        $this->assertStringContainsString('id="apply"', $html);
        $this->assertStringContainsString('action="'.route('careers.apply', $job).'"', $html);
        $this->get('/careers/'.$inactive->id)->assertNotFound();
    }

    public function test_application_is_stored_privately_and_counted_in_the_admin(): void
    {
        Storage::fake('local');
        $job = JobOpening::factory()->create(['title' => 'مدیر فروش']);

        $this->from('/careers')->post('/careers/'.$job->id.'/apply', [
            'job_id' => $job->id, 'phone' => '۰۹۱۲ ۳۴۵ ۶۷۸۹',
            'resume' => UploadedFile::fake()->create('cv.pdf', 300, 'application/pdf'),
        ])->assertRedirect('/careers#job-'.$job->id)->assertSessionHas('job_applied', $job->id)->assertSessionHasNoErrors();

        $application = JobApplication::firstOrFail();
        $this->assertSame('09123456789', $application->phone);
        $this->assertSame('مدیر فروش', $application->job_title);
        $this->assertSame('cv.pdf', $application->resume_name);
        $this->assertStringStartsWith('resumes/', $application->resume_path);
        Storage::disk('local')->assertExists($application->resume_path);
        $this->assertNull($application->seen_at);
        $this->assertSame(1, JobApplication::unseenCount());

        // The success state re-opens the form of that opening.
        $html = $this->withSession(['job_applied' => $job->id])->get('/careers')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/id="job-apply-'.$job->id.'"[^>]*data-auto-open/', $html);
        $this->assertStringContainsString(__('careers.form.sent'), $html);

        // Admin: badge, inbox, opening marks seen, résumé download, delete removes the file.
        $admin = $this->adminUser('admin');
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('pg-admin__nav-badge', false);
        $this->actingAs($admin)->get('/admin/applications')->assertOk()->assertSee('مدیر فروش')->assertSee(__('admin.applications.new'));
        $this->actingAs($admin)->get('/admin/applications/'.$application->id)->assertOk();
        $this->assertNotNull($application->fresh()->seen_at);
        $this->assertSame(0, JobApplication::unseenCount());
        $this->actingAs($admin)->get('/admin/applications/'.$application->id.'/resume')->assertOk()->assertDownload('cv.pdf');
        $this->actingAs($admin)->delete('/admin/applications/'.$application->id)->assertRedirect('/admin/applications');
        Storage::disk('local')->assertMissing($application->resume_path);
    }

    public function test_validation_rejects_bad_phone_file_type_size_and_honeypot(): void
    {
        Storage::fake('local');
        $job = JobOpening::factory()->create();

        $this->from('/careers')->post('/careers/'.$job->id.'/apply', [
            'job_id' => $job->id, 'phone' => '12', 'resume' => UploadedFile::fake()->create('cv.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors(['phone', 'resume']);

        $this->post('/careers/'.$job->id.'/apply', [
            'phone' => '09123456789', 'resume' => UploadedFile::fake()->create('cv.pdf', 6000, 'application/pdf'),
        ])->assertSessionHasErrors(['resume']);

        $this->post('/careers/'.$job->id.'/apply', [
            'phone' => '09123456789', 'resume' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf'), 'website' => 'spam',
        ])->assertSessionHasErrors(['website']);

        $this->assertSame(0, JobApplication::count());
        $this->get('/admin/applications')->assertRedirect('/admin/login');
    }

    public function test_editor_can_see_but_not_delete_applications(): void
    {
        $application = JobApplication::factory()->create();
        $editor = $this->adminUser('editor');

        $this->actingAs($editor)->get('/admin/applications')->assertOk();
        $this->actingAs($editor)->delete('/admin/applications/'.$application->id)->assertForbidden();
    }

    public function test_admin_saves_body_and_specs_on_an_opening(): void
    {
        $admin = $this->adminUser('admin');

        $this->actingAs($admin)->post('/admin/jobs', [
            'title' => 'x', 'tone' => 'blue', 'body' => "# عنوان\n- مورد", 'specs_text' => "جنسیت | تفاوتی ندارد\nبدون مقدار\n | بدون عنوان",
        ])->assertSessionHasNoErrors()->assertRedirect();

        $job = JobOpening::firstOrFail();
        $this->assertSame("# عنوان\n- مورد", $job->body);
        $this->assertSame([['label' => 'جنسیت', 'value' => 'تفاوتی ندارد'], ['label' => 'بدون مقدار', 'value' => '']], $job->specs);
        $this->assertSame("جنسیت | تفاوتی ندارد\nبدون مقدار | ", $job->specsAsText());
        $this->assertTrue($job->hasDetails());
    }
}
