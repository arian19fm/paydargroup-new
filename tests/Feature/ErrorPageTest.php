<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_unknown_url_returns_404_with_branded_page(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('<html lang="fa" dir="rtl">', false)
            ->assertSee(__('errors.404.title'))
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<title>'.__('errors.404.title').' | Paydar Group</title>', false);
    }
}
