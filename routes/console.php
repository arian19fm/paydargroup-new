<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatic news (App\Support\News\NewsRunner): checks every minute whether a
// run was requested in the admin or the day's run is due. Needs the server
// cron entry `* * * * * php artisan schedule:run` (docs/CMS.md).
Schedule::command('news:tick')->everyMinute()->withoutOverlapping(60);
