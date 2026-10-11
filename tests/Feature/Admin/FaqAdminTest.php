<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class FaqAdminTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_designed_questions_are_imported_and_listed_in_the_admin(): void
    {
        $this->assertSame(5, Faq::count());
        $first = Faq::query()->ordered()->firstOrFail();
        $this->assertSame('پایدار گروپ چیست و چه خدماتی ارائه می‌دهد؟', $first->question);
        $this->assertSame(0, Faq::whereNull('answer')->count()); // answered by the content migration

        $this->actingAs($this->adminUser('editor'))->get('/admin/faqs')->assertOk()
            ->assertSee($first->question);

        $html = $this->get('/')->getContent();
        $this->assertSame(5, substr_count($html, 'data-bs-toggle="collapse"'));
        $this->assertStringNotContainsString(__('home.faq.answer_pending'), $html);
    }

    public function test_answer_uses_the_rich_editor_and_is_sanitised_on_save(): void
    {
        $faq = Faq::create(['question' => 'پرسش', 'answer' => '<p>خط اول</p>', 'is_active' => true]);
        $editor = $this->adminUser('editor');

        $html = $this->actingAs($editor)->get("/admin/faqs/{$faq->id}/edit")->assertOk()->getContent();

        $this->assertStringContainsString('data-rich-editor', $html);
        $this->assertStringContainsString('data-upload-url="'.route('admin.media.editor-image').'"', $html);
        $this->assertStringContainsString('&lt;p&gt;خط اول&lt;/p&gt;</textarea>', $html);

        $this->actingAs($editor)->put("/admin/faqs/{$faq->id}", [
            'question' => 'پرسش', 'answer' => '<p onclick="x()">پاسخ <a href="javascript:alert(1)">بد</a></p><script>alert(1)</script>',
        ])->assertSessionHasNoErrors();
        $this->assertSame('<p>پاسخ <a>بد</a></p>', $faq->refresh()->answer);

        $this->actingAs($editor)->put("/admin/faqs/{$faq->id}", ['question' => 'پرسش', 'answer' => '<p></p>'])->assertSessionHasNoErrors();
        $this->assertNull($faq->refresh()->answer);
    }

    public function test_editor_manages_questions(): void
    {
        Faq::query()->delete();
        $editor = $this->adminUser('editor');

        $this->actingAs($editor)->get('/admin/faqs')->assertOk();
        $this->actingAs($editor)->get('/admin/faqs/create')->assertOk();
        $this->actingAs($editor)->post('/admin/faqs', [
            'question' => 'گروه پایدار در چه حوزه‌هایی فعال است؟', 'answer' => 'پاسخ آزمایشی', 'sort_order' => 2, 'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/faqs');

        $faq = Faq::firstOrFail();
        $this->assertTrue($faq->is_active);
        $this->assertSame(2, $faq->sort_order);

        $this->actingAs($editor)->get("/admin/faqs/{$faq->id}/edit")->assertOk()->assertSee('پاسخ آزمایشی');
        $this->actingAs($editor)->put("/admin/faqs/{$faq->id}", [
            'question' => 'پرسش ویرایش‌شده', 'answer' => '',
        ])->assertSessionHasNoErrors();
        $this->assertNull($faq->refresh()->answer);

        $this->actingAs($editor)->put("/admin/faqs/{$faq->id}", [
            'question' => 'پرسش ویرایش‌شده', 'answer' => 'پاسخ تازه',
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/faqs');
        $faq->refresh();
        $this->assertSame('پاسخ تازه', $faq->answer);
        $this->assertFalse($faq->is_active);
        $this->assertSame(0, $faq->sort_order);

        $this->actingAs($editor)->get('/admin/faqs?q=ویرایش')->assertOk()->assertSee('پرسش ویرایش‌شده');
        $this->actingAs($editor)->delete("/admin/faqs/{$faq->id}")->assertRedirect('/admin/faqs');
        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_validation_and_access(): void
    {
        $admin = $this->adminUser('admin');

        $this->actingAs($admin)->post('/admin/faqs', ['question' => '', 'answer' => ''])
            ->assertSessionHasErrors(['question'])->assertSessionDoesntHaveErrors(['answer']);
        $this->actingAs($admin)->post('/admin/faqs', ['question' => str_repeat('a', 501), 'answer' => 'x'])
            ->assertSessionHasErrors(['question']);

        auth()->logout();
        $this->get('/admin/faqs')->assertRedirect('/admin/login');
    }

    public function test_home_page_renders_active_questions_in_order(): void
    {
        Faq::query()->delete();
        Faq::factory()->create(['question' => 'پرسش دوم', 'answer' => 'پاسخ دوم', 'sort_order' => 2]);
        Faq::factory()->create(['question' => 'پرسش اول', 'answer' => 'پاسخ اول', 'sort_order' => 1]);
        Faq::factory()->create(['question' => 'پرسش غیرفعال', 'is_active' => false]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'data-bs-toggle="collapse"'));
        $this->assertLessThan(strpos($html, 'پرسش دوم'), strpos($html, 'پرسش اول'));
        $this->assertStringContainsString('پاسخ اول', $html);
        $this->assertStringNotContainsString('پرسش غیرفعال', $html);
        $this->assertStringNotContainsString('توکن‌سازی دارایی چیست', $html);
    }

    public function test_home_page_hides_the_list_when_no_question_is_active(): void
    {
        Faq::query()->update(['is_active' => false]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('id="faq"', $html);
        $this->assertStringNotContainsString('id="faq-accordion"', $html);
    }
}
