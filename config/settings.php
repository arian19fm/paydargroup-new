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

        'contact' => [
            'label' => 'settings.groups.contact',
            'keys' => [
                'email' => ['type' => 'email', 'public' => true, 'label' => 'settings.contact.email'],
                'phone' => ['type' => 'string', 'public' => true, 'label' => 'settings.contact.phone'],
                'address' => ['type' => 'text', 'public' => true, 'label' => 'settings.contact.address'],
                'working_hours' => ['type' => 'string', 'public' => true, 'label' => 'settings.contact.working_hours'],
                // Contact page map: a static map image from the media library, optionally linking to a maps URL.
                'map_image_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.contact.map_image'],
                'map_url' => ['type' => 'url', 'public' => true, 'label' => 'settings.contact.map_url'],
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
