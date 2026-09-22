<?php

namespace Tests\Feature;

use App\Models\ContactRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('contact-form');
    }

    public function test_home_page_renders_the_contact_form(): void
    {
        $this->get('/')
            ->assertSee('action="'.route('contact.store').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="message"', false)
            ->assertSee('name="website"', false);
    }

    public function test_valid_request_is_stored_and_confirmed(): void
    {
        $response = $this->from('/')->post(route('contact.store'), [
            'name' => 'کاربر آزمایشی',
            'phone' => '۰۹۱۲ ۳۴۵ ۶۷۸۹',
            'message' => 'لطفاً درباره طرح‌های سرمایه‌گذاری با من تماس بگیرید.',
            'website' => '',
        ]);

        $response->assertRedirect(route('home').'#contact')->assertSessionHas('contact_sent', true);

        $this->assertDatabaseHas('contact_requests', [
            'name' => 'کاربر آزمایشی',
            'phone' => '09123456789',
        ]);
        $this->assertSame(1, ContactRequest::count());

        $this->get('/')->assertSee(__('home.contact.sent'));
    }

    public function test_phone_and_message_are_required(): void
    {
        $this->from('/')->post(route('contact.store'), ['name' => 'x'])
            ->assertRedirect('/')
            ->assertSessionHasErrors(['phone', 'message']);

        $this->assertSame(0, ContactRequest::count());
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $this->from('/')->post(route('contact.store'), [
            'phone' => 'not-a-phone',
            'message' => 'پیام آزمایشی با طول کافی است.',
        ])->assertSessionHasErrors(['phone']);

        $this->assertSame(0, ContactRequest::count());
    }

    public function test_honeypot_rejects_bots(): void
    {
        $this->from('/')->post(route('contact.store'), [
            'phone' => '09123456789',
            'message' => 'پیام آزمایشی با طول کافی است.',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors(['website']);

        $this->assertSame(0, ContactRequest::count());
    }

    public function test_submissions_are_rate_limited_per_ip(): void
    {
        $payload = ['phone' => '09123456789', 'message' => 'پیام آزمایشی با طول کافی است.'];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.store'), $payload)->assertRedirect();
        }

        $this->post(route('contact.store'), $payload)->assertStatus(429);
        $this->assertSame(5, ContactRequest::count());
    }
}
