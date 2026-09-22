<?php

return [
    'groups' => ['general' => 'General', 'seo' => 'SEO', 'home' => 'Home page', 'contact' => 'Contact', 'social' => 'Social'],
    'general' => [
        'site_name' => 'Site name', 'site_tagline' => 'Tagline', 'logo' => 'Logo (media ID)',
        'footer_text' => 'Footer text', 'copyright_text' => 'Copyright text',
    ],
    'seo' => [
        'default_title' => 'Default home page title', 'default_description' => 'Default meta description',
        'default_og_image' => 'Default sharing image (media ID)', 'twitter_site' => 'Twitter/X handle (e.g. @paydargroup)',
        'title_separator' => 'Title separator (default " | ")',
    ],
    'home' => [
        'hero_video' => 'Hero background video (media ID of an uploaded MP4/WebM)',
        'hero_image' => 'Hero image (media ID; blank = designed photo — with a video it is the poster / no-motion fallback)',
    ],
    'contact' => ['email' => 'E-mail', 'phone' => 'Phone', 'address' => 'Address', 'working_hours' => 'Working hours'],
    'social' => ['instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'telegram' => 'Telegram', 'x' => 'X (Twitter)', 'aparat' => 'Aparat', 'youtube' => 'YouTube'],
];
