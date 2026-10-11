<?php

/*
|--------------------------------------------------------------------------
| Automatic news (admin → اخبار خودکار)
|--------------------------------------------------------------------------
|
| Technical knobs for the news writer (App\Support\News). What editors
| control — on/off, time of day, articles per run, publish or draft,
| category, author, cover — lives in the hidden `news` settings group and
| is edited on its own admin page. The only secret is ANTHROPIC_API_KEY
| (config/services.php).
|
*/

return [

    'model' => env('NEWS_MODEL', 'claude-opus-5-5'),

    // low | medium | high | xhigh | max — thinking depth and token spend.
    'effort' => env('NEWS_EFFORT', 'high'),

    // Web searches Claude may run per run (each costs; see the API pricing).
    'max_searches' => (int) env('NEWS_MAX_SEARCHES', 8),

    // Server-side pauses (long search loops) we resume before giving up.
    'max_continuations' => 4,

    'max_tokens' => 32000,

    // Seconds for one API request (searching and writing take minutes).
    'timeout' => 600,

    // The admin's "time of day" is in this zone.
    'timezone' => env('NEWS_TIMEZONE', 'Asia/Tehran'),

    'max_articles_per_run' => 5,

    // A run still "running" after this many minutes is marked failed (the
    // process died); the next tick can then run again.
    'stale_after_minutes' => 30,

    // Used when the admin leaves the topics field blank.
    'default_topics' => [
        'بازارهای مالی جهانی و ایران (بورس، طلا، ارز، نرخ بهره)',
        'سرمایه‌گذاری و تأمین مالی (صندوق‌ها، استارتاپ‌ها، تأمین مالی جمعی، توکن‌سازی دارایی)',
        'رمزارزها و بلاک‌چین (بیت‌کوین، اتریوم، مقررات، بازار)',
    ],

    // US$ per million tokens / per 1,000 searches, for the admin's rough
    // cost column only (claude-opus-5-5 list prices).
    'pricing' => [
        'input' => 4.0,
        'output' => 20.0,
        'searches' => 10.0,
    ],

];
