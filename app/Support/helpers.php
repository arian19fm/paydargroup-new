<?php

use App\Support\Seo\SeoManager;

if (! function_exists('seo')) {
    /**
     * The request-scoped SEO manager. Set page metadata from controllers or
     * views: seo()->title('...')->description('...').
     */
    function seo(): SeoManager
    {
        return app(SeoManager::class);
    }
}
