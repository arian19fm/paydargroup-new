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
| Types: string, text, boolean, integer, url, link (https URL, /path or
| #anchor), email, json, media (media id)
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

        // Every text, link and image of the home page, grouped by section
        // (`section` starts a heading in the admin form). The designed copy
        // was written into these rows by the
        // 2026_09_28_130000_fill_home_page_settings migration, so the admin
        // shows exactly what the page renders; a blank text is not rendered.
        // Blank links fall back to the natural target (noted in each label).
        'home' => [
            'label' => 'settings.groups.home',
            'keys' => [
                // Hero. With a background video the image (or the designed photo) is its poster / no-motion fallback.
                'hero_video_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.home.hero_video', 'section' => 'settings.home.sections.hero'],
                'hero_image_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.home.hero_image'],
                'hero_eyebrow' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.hero_eyebrow'],
                'hero_title_line_1' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.hero_title_line_1'],
                'hero_title_line_2' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.hero_title_line_2'],
                'hero_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.home.hero_text'],
                'hero_cta_label' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.hero_cta_label'],
                'hero_cta_url' => ['type' => 'link', 'public' => true, 'label' => 'settings.home.hero_cta_url'],
                // Businesses ("our products"); the cards themselves are admin → businesses.
                'products_eyebrow' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.products_eyebrow', 'section' => 'settings.home.sections.products'],
                'products_title_highlight' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.products_title_highlight'],
                'products_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.products_title'],
                'products_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.home.products_text'],
                'products_cta_label' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.products_cta_label'],
                'products_cta_url' => ['type' => 'link', 'public' => true, 'label' => 'settings.home.products_cta_url'],
                'products_card_cta_label' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.products_card_cta_label'],
                // Latest articles; the cards are the published articles.
                'blog_eyebrow' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.blog_eyebrow', 'section' => 'settings.home.sections.blog'],
                'blog_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.blog_title'],
                'blog_title_highlight' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.blog_title_highlight'],
                'blog_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.home.blog_text'],
                'blog_cta_label' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.blog_cta_label'],
                'blog_cta_url' => ['type' => 'link', 'public' => true, 'label' => 'settings.home.blog_cta_url'],
                'blog_empty_text' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.blog_empty_text'],
                // FAQ; the questions themselves are admin → FAQ.
                'faq_eyebrow' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.faq_eyebrow', 'section' => 'settings.home.sections.faq'],
                'faq_title_highlight' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.faq_title_highlight'],
                'faq_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.faq_title'],
                'faq_ask_placeholder' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.faq_ask_placeholder'],
                'faq_card_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.faq_card_title'],
                'faq_card_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.home.faq_card_text'],
                'faq_card_cta_label' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.faq_card_cta_label'],
                'faq_card_cta_url' => ['type' => 'link', 'public' => true, 'label' => 'settings.home.faq_card_cta_url'],
                // Contact form section.
                'contact_image_media_id' => ['type' => 'media', 'public' => true, 'label' => 'settings.home.contact_image', 'section' => 'settings.home.sections.contact'],
                'contact_eyebrow' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.contact_eyebrow'],
                'contact_title' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.contact_title'],
                'contact_text' => ['type' => 'text', 'public' => true, 'label' => 'settings.home.contact_text'],
                'contact_submit_label' => ['type' => 'string', 'public' => true, 'label' => 'settings.home.contact_submit_label'],
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
