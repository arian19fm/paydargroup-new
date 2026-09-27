<?php

return [
    'groups' => ['general' => 'General', 'seo' => 'SEO', 'home' => 'Home page', 'about' => 'About page', 'careers' => 'Careers page', 'contact' => 'Contact', 'social' => 'Social'],
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
        'products_eyebrow' => 'Businesses section: eyebrow above the title (blank = designed copy)',
        'products_title_highlight' => 'Businesses section: highlighted part of the title (e.g. "Our products;")',
        'products_title' => 'Businesses section: rest of the title',
        'products_text' => 'Businesses section: description',
        'products_cta_label' => 'Businesses section: button label (blank = "See more")',
        'products_cta_url' => 'Businesses section: button link (blank = the "products" page when published)',
    ],
    'about' => [
        'image_1' => 'First photo under the intro (media ID)',
        'image_2' => 'Second photo under the intro (media ID)',
        'partner_media_ids' => 'Partner logos (comma-separated media IDs; blank hides the strip)',
        'history_title' => 'History section title',
        'history_text' => 'History section text',
        'history_image' => 'History section photo (media ID)',
        'stat_clients' => 'Stat: loyal clients',
        'stat_years' => 'Stat: years of experience',
        'stat_companies' => 'Stat: subsidiary companies',
    ],
    'careers' => [
        'hero_eyebrow' => 'Eyebrow above the title (blank = designed copy)', 'hero_title' => 'Page title', 'hero_image' => 'Top photo (media ID; blank = designed photo)',
        'benefits_eyebrow' => 'Benefits: eyebrow', 'benefits_title_highlight' => 'Benefits: highlighted part of the title', 'benefits_title' => 'Benefits: rest of the title',
        'benefits_text' => 'Benefits: text', 'benefits_cta_label' => 'Benefits: button label', 'benefits_cta_url' => 'Benefits: button link (blank = contact page)',
        'benefit_1_title' => 'Benefit card 1 (book icon): title', 'benefit_1_text' => 'Benefit card 1: text',
        'benefit_2_title' => 'Benefit card 2 (tick icon): title', 'benefit_2_text' => 'Benefit card 2: text',
        'benefit_3_title' => 'Benefit card 3 (chart icon): title', 'benefit_3_text' => 'Benefit card 3: text',
        'benefit_4_title' => 'Benefit card 4 (money icon): title', 'benefit_4_text' => 'Benefit card 4: text',
        'jobs_eyebrow' => 'Openings: eyebrow', 'jobs_title_highlight' => 'Openings: highlighted part of the title', 'jobs_title' => 'Openings: rest of the title',
        'jobs_text' => 'Openings: text', 'jobs_empty' => 'Text shown when no opening is active',
    ],
    'contact' => ['email' => 'E-mail', 'phone' => 'Phone', 'address' => 'Address', 'working_hours' => 'Working hours',
        'map_url' => 'Google Maps link (share link, place page URL or embed code); shown as a live map on the contact page', 'map_image' => 'Fallback map image (media ID; used only when the Google Maps link is blank or cannot be embedded)'],
    'social' => ['instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'telegram' => 'Telegram', 'x' => 'X (Twitter)', 'aparat' => 'Aparat', 'youtube' => 'YouTube'],
];
