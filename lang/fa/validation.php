<?php

// Only project-specific messages; framework rules fall back to English
// (lang/en/validation.php from the framework) until a full Persian set is added.
return [
    'custom' => [
        'slug' => [
            'format' => 'نامک فقط می‌تواند شامل حروف کوچک انگلیسی، عدد و خط تیره باشد.',
            'reserved' => 'این نامک رزرو شده است و قابل استفاده نیست.',
        ],
        'redirect' => [
            'protected' => 'این مسیر متعلق به سیستم است و قابل ریدایرکت نیست.',
            'root' => 'صفحهٔ اصلی قابل ریدایرکت نیست.',
            'loop' => 'این ریدایرکت یک حلقه ایجاد می‌کند.',
        ],
        'menu_item' => [
            'target' => 'دقیقاً یکی از «صفحه» یا «آدرس» باید مشخص شود.',
            'cycle' => 'یک آیتم نمی‌تواند زیرمجموعهٔ خودش باشد.',
        ],
    ],
];
