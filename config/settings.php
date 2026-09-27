<?php

/*
|--------------------------------------------------------------------------
| Site settings schema
|--------------------------------------------------------------------------
|
| Declares every editable setting: its group, key, type, whether it is
| public (safe to expose to the frontend/APIs) and the admin label. The
| database only stores values; keys not declared here cannot be set.
| All defaults are intentionally blank — never seed company data here.
|
| Types: string, text, boolean, integer, url, email, json, media (media id)
|
*/

return [

    'groups' => [

        'general' => [
            'label' => 'settings.groups.general',
            'keys' => [
                'site_name' => ['type' => 'string', 'public' => true, 'label' => 'settings.general.site_name'],
                'site_tagline' => ['type' => 'string', 'public' => true, 'label' => 'settings.general.site_tagline'],
                'logo_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.general.logo'],
                'footer_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.general.footer_text'],
                'copyright_text' => ['type' => 'string', 'public' => true, 'label' => 'settings.general.copyright_text'],
            ],
        ],

        'seo' => [
            'label' => 'settings.groups.seo',
            'keys' => [
                'default_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.seo.default_title'],
                'default_description' => ['type' => 'text', 'public' => true, 'label' => 'settings.seo.default_description'],
                'default_og_image_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.seo.default_og_image'],
                'twitter_site' => ['type' => 'string', 'public' => true, 'label' => 'settings.seo.twitter_site'],
                'title_separator' => ['type' => 'string', 'public' => true, 'label' => 'settings.seo.title_separator'],
            ],
        ],

        'home' => [
            'label' => 'settings.groups.home',
            'keys' => [
                // Background video for the hero (MP4/WebM from the media library). When set,
                // the image below (or the default photo) is its poster / no-motion fallback.
                'hero_video_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.home.hero_video'],
                // Optional replacement for the designed hero photograph.
                'hero_image_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.home.hero_image'],
                // Businesses ("our products") section copy; blank = the designed text in lang/{locale}/home.php.
                'products_eyebrow' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.products_eyebrow'],
                'products_title_highlight' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.products_title_highlight'],
                'products_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.products_title'],
                'products_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.home.products_text'],
                'products_cta_label' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.products_cta_label'],
                'products_cta_url' => ['type' => 'url', 'public' => true, 'label' => 'settings.home.products_cta_url'],
            ],
        ],

        // "About" page template extras (the page itself — title, lead,
        // body — is the CMS page that uses the template).
        'about' => [
            'label' => 'settings.groups.about',
            'keys' => [
                'image_1_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.about.image_1'],
                'image_2_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.about.image_2'],
                'partner_media_ids' => ['type' => 'string', 'public' => true, 'label' => 'settings.about.partner_media_ids'],
                'history_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.about.history_title'],
                'history_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.about.history_text'],
                'history_image_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.about.history_image'],
                'stat_clients' => ['type' => 'integer', 'public' => true, 'label' => 'settings.about.stat_clients'],
                'stat_years' => ['type' => 'integer', 'public' => true, 'label' => 'settings.about.stat_years'],
                'stat_companies' => ['type' => 'integer', 'public' => true, 'label' => 'settings.about.stat_companies'],
            ],
        ],

        // Careers page copy and benefit cards (blank = the designed text in lang/{locale}/careers.php).
        'careers' => [
            'label' => 'settings.groups.careers',
            'keys' => [
                'hero_eyebrow' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.hero_eyebrow'],
                'hero_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.hero_title'],
                'hero_image_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.careers.hero_image'],
                'benefits_eyebrow' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.benefits_eyebrow'],
                'benefits_title_highlight' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.benefits_title_highlight'],
                'benefits_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.benefits_title'],
                'benefits_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.careers.benefits_text'],
                'benefits_cta_label' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.benefits_cta_label'],
                'benefits_cta_url' => ['type' => 'url', 'public' => true, 'label' => 'settings.careers.benefits_cta_url'],
                'benefit_1_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.benefit_1_title'],
                'benefit_1_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.careers.benefit_1_text'],
                'benefit_2_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.benefit_2_title'],
                'benefit_2_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.careers.benefit_2_text'],
                'benefit_3_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.benefit_3_title'],
                'benefit_3_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.careers.benefit_3_text'],
                'benefit_4_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.benefit_4_title'],
                'benefit_4_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.careers.benefit_4_text'],
                'jobs_eyebrow' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.jobs_eyebrow'],
                'jobs_title_highlight' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.jobs_title_highlight'],
                'jobs_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.jobs_title'],
                'jobs_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.careers.jobs_text'],
                'jobs_empty' => ['type' => 'string', 'public' => true, 'label' => 'settings.careers.jobs_empty'],
            ],
        ],

        'contact' => [
            'label' => 'settings.groups.contact',
            'keys' => [
                'email' => ['type' => 'email', 'public' => true, 'label' => 'settings.contact.email'],
                'phone' => ['type' => 'string', 'public' => true, 'label' => 'settings.contact.phone'],
                'address' => ['type' => 'text', 'public' => true, 'label' => 'settings.contact.address'],
                'working_hours' => ['type' => 'string', 'public' => true, 'label' => 'settings.contact.working_hours'],
                // Contact page map: a Google Maps link (embedded live via App\Support\Contact\GoogleMapsEmbed),
                // with a static image from the media library as the fallback.
                'map_url' => ['type' => 'text', 'public' => true, 'label' => 'settings.contact.map_url'],
                'map_image_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.contact.map_image'],
            ],
        ],

        'social' => [
            'label' => 'settings.groups.social',
            'keys' => [
                'instagram' => ['type' => 'url', 'public' => true, 'label' => 'settings.social.instagram'],
                'linkedin' => ['type' => 'url', 'public' => true, 'label' => 'settings.social.linkedin'],
                'telegram' => ['type' => 'url', 'public' => true, 'label' => 'settings.social.telegram'],
                'x' => ['type' => 'url', 'public' => true, 'label' => 'settings.social.x'],
                'aparat' => ['type' => 'url', 'public' => true, 'label' => 'settings.social.aparat'],
                'youtube' => ['type' => 'url', 'public' => true, 'label' => 'settings.social.youtube'],
            ],
        ],

    ],

    'cache_key' => 'settings.all',

];
