<?php

namespace Tests;

use App\Support\Seo\SeoManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Keep the content the import migrations move into the database
     * (designed businesses, the fixed pages and the designed images in the
     * media library). Off by default, so tests start from empty
     * businesses/pages/media tables.
     */
    protected bool $keepImportedContent = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Tests must not depend on a Vite build being present.
        $this->withoutVite();

        // Deterministic absolute URLs for canonical / JSON-LD assertions.
        config(['app.url' => 'https://paydargroup.test']);

        if (! $this->keepImportedContent && in_array(RefreshDatabase::class, class_uses_recursive($this), true)) {
            DB::table('businesses')->delete();
            DB::table('pages')->delete();
            DB::table('media')->delete();
        }
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
