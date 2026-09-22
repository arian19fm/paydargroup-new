<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_staff_can_change_their_own_name_email_and_password(): void
    {
        $user = $this->editor(['password' => 'OldPassword1234']);

        $this->actingAs($user)->get('/admin/profile')->assertOk()->assertSee('name="current_password"', false);

        $this->actingAs($user)->put('/admin/profile', [
            'name' => 'نام جدید',
            'email' => 'new@example.com',
            'current_password' => 'OldPassword1234',
            'password' => 'NewPassword5678',
            'password_confirmation' => 'NewPassword5678',
        ])->assertRedirect('/admin/profile')->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('نام جدید', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertTrue(Hash::check('NewPassword5678', $user->password));
    }

    public function test_changing_password_requires_the_current_one(): void
    {
        $user = $this->editor(['password' => 'OldPassword1234']);

        $this->actingAs($user)->put('/admin/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'wrong-password-1',
            'password' => 'NewPassword5678',
            'password_confirmation' => 'NewPassword5678',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('OldPassword1234', $user->fresh()->password));
    }

    public function test_name_only_change_needs_no_password(): void
    {
        $user = $this->editor();

        $this->actingAs($user)->put('/admin/profile', ['name' => 'فقط نام', 'email' => $user->email])
            ->assertSessionHasNoErrors();

        $this->assertSame('فقط نام', $user->fresh()->name);
    }
}
