<?php

use App\Http\Controllers\Site\HomeController;
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
