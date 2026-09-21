<?php

namespace Tests;

use App\Support\Seo\SeoManager;
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

    /**
     * Every simulated request gets a fresh request-scoped SEO state, as it
     * would in a real HTTP request (scoped bindings persist inside one test).
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->app->forgetInstance(SeoManager::class);

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }
}
