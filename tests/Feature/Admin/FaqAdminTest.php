<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class FaqAdminTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_editor_manages_questions(): void
    {
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
            ->assertSessionHasErrors(['question', 'answer']);
        $this->actingAs($admin)->post('/admin/faqs', ['question' => str_repeat('a', 501), 'answer' => 'x'])
            ->assertSessionHasErrors(['question']);

        auth()->logout();
        $this->get('/admin/faqs')->assertRedirect('/admin/login');
    }

    public function test_home_page_renders_active_questions_in_order(): void
    {
        Faq::factory()->create(['question' => 'پرسش دوم', 'answer' => 'پاسخ دوم', 'sort_order' => 2]);
        Faq::factory()->create(['question' => 'پرسش اول', 'answer' => 'پاسخ اول', 'sort_order' => 1]);
        Faq::factory()->create(['question' => 'پرسش غیرفعال', 'is_active' => false]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'data-bs-toggle="collapse"'));
        $this->assertLessThan(strpos($html, 'پرسش دوم'), strpos($html, 'پرسش اول'));
        $this->assertStringContainsString('پاسخ اول', $html);
        $this->assertStringNotContainsString('پرسش غیرفعال', $html);
        $this->assertStringNotContainsString(__('home.faq.items')[0]['question'], $html);
    }

    public function test_home_page_hides_the_list_when_no_question_is_active(): void
    {
        Faq::factory()->create(['is_active' => false]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('id="faq"', $html);
        $this->assertStringNotContainsString('id="faq-accordion"', $html);
    }
}
