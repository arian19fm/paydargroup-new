<?php

use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\RobotsController;
use App\Http\Controllers\Site\SitemapController;
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

// SEO endpoints (environment aware — see config/seo.php).
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
