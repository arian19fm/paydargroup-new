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

        'contact' => [
            'label' => 'settings.groups.contact',
            'keys' => [
                'email' => ['type' => 'email', 'public' => true, 'label' => 'settings.contact.email'],
                'phone' => ['type' => 'string', 'public' => true, 'label' => 'settings.contact.phone'],
                'address' => ['type' => 'text', 'public' => true, 'label' => 'settings.contact.address'],
                'working_hours' => ['type' => 'string', 'public' => true, 'label' => 'settings.contact.working_hours'],
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
