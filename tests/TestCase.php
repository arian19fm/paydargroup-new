<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests must not depend on a Vite build being present.
        $this->withoutVite();

        // Deterministic absolute URLs for canonical / JSON-LD assertions.
        config(['app.url' => 'https://paydargroup.test']);
    }
}
