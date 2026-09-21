<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Seo\SeoManager;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/**
 * Environment-aware robots.txt. Non-indexable environments (local, staging)
 * disallow everything; production allows normal crawling and advertises the
 * sitemap. CSS/JS/build assets are never blocked — search engines need them
 * to render pages.
 */
class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $lines = ['User-agent: *'];

        if (config('seo.indexable')) {
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /admin/';
            $lines[] = 'Allow: /';

            if (Route::has('sitemap')) {
                $lines[] = '';
                $lines[] = 'Sitemap: '.SeoManager::absoluteUrl('sitemap.xml');
            }
        } else {
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
