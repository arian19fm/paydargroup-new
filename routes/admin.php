<?php

use App\Http\Controllers\Admin\ArticleCategoryController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\JobApplicationController;
use App\Http\Controllers\Admin\JobOpeningController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TeamGroupController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel routes
|--------------------------------------------------------------------------
|
| Registered in bootstrap/app.php under the "/admin" prefix with the
| "admin." route-name prefix and the "web" middleware group. Staff-only:
| there is no registration or password reset in this phase.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    // Pages are fixed (config/cms.php → fixed_pages): edit only, no create/delete.
    Route::resource('pages', PageController::class)->only(['index', 'edit', 'update']);
    Route::resource('articles', ArticleController::class)->except('show');
    Route::resource('businesses', BusinessController::class)->except('show');
    Route::resource('jobs', JobOpeningController::class)->except('show')->parameters(['jobs' => 'job']);
    Route::resource('applications', JobApplicationController::class)->only(['index', 'show', 'destroy'])->parameters(['applications' => 'application']);
    Route::get('applications/{application}/resume', [JobApplicationController::class, 'resume'])->name('applications.resume');
    Route::resource('faqs', FaqController::class)->except('show');
    Route::resource('categories', ArticleCategoryController::class)->except('show');
    Route::resource('redirects', RedirectController::class)->except('show');
    Route::resource('media', MediaController::class)->except('show');
    Route::resource('team/groups', TeamGroupController::class)->except('show')->names('team.groups')->parameters(['groups' => 'group']);
    Route::resource('team/members', TeamMemberController::class)->except('show')->names('team.members')->parameters(['members' => 'member']);

    Route::resource('menus', MenuController::class)->except('show');
    Route::post('menus/{menu}/items', [MenuItemController::class, 'store'])->name('menus.items.store');
    Route::put('menus/{menu}/items/{item}', [MenuItemController::class, 'update'])->name('menus.items.update');
    Route::delete('menus/{menu}/items/{item}', [MenuItemController::class, 'destroy'])->name('menus.items.destroy');

    Route::middleware('can:settings.view')->group(function () {
        Route::get('settings/{group}', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{group}', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::resource('roles', RoleController::class)->except('show');

    // Own account (any staff member).
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
});
