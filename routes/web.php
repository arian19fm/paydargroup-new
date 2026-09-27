<?php

use App\Http\Controllers\Site\ArticleController;
use App\Http\Controllers\Site\ContactPageController;
use App\Http\Controllers\Site\ContactRequestController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\RobotsController;
use App\Http\Controllers\Site\SitemapController;
use App\Http\Controllers\Site\TeamPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site routes
|--------------------------------------------------------------------------
|
| Server-rendered corporate pages. Keep URLs clean, lowercase and stable:
| they are indexed by search engines. Admin routes live in routes/admin.php.
|
*/

Route::get('/', HomeController::class)->name('home');

// Contact page + the form it shares with the home page. Submissions are
// rate limited per IP (see AppServiceProvider).
Route::get('/contact', ContactPageController::class)->name('contact');
Route::get('/team', TeamPageController::class)->name('team');
Route::post('/contact', [ContactRequestController::class, 'store'])
    ->middleware('throttle:contact-form')
    ->name('contact.store');

// SEO endpoints (environment aware — see config/seo.php).
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Managed content. Only published items resolve (see the controllers).
$slug = '[a-z0-9]+(?:-[a-z0-9]+)*';

Route::get('/articles/{slug}', [ArticleController::class, 'show'])->where('slug', $slug)->name('articles.show');

// Single-segment page slugs. This is the ONLY catch-all and it stays last:
// the negative lookahead excludes every reserved path (admin, build, storage,
// up, robots.txt, sitemap.xml, ...) so it can never shadow application routes.
$reserved = implode('|', array_map(fn ($r) => preg_quote($r, '#'), config('cms.reserved_slugs')));

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', "(?!(?:{$reserved})\$){$slug}")
    ->name('pages.show');
