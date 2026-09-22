<?php

/*
|--------------------------------------------------------------------------
| Home page structure
|--------------------------------------------------------------------------
|
| Non-textual structure of the public home page: the product line-up, its
| assets and accent keys, and the CMS page slugs the calls to action point
| at. Copy lives in lang/{locale}/home.php; articles, menus and settings
| come from the CMS. A CTA whose page is not published renders without a
| link, so nothing here can produce a dead link.
|
*/

return [

    // Ordered product line-up. `key` selects the copy (home.products.items.*)
    // and the accent colour (resources/scss/abstracts/_variables.scss);
    // `image` is the Figma export under public/images/home; `slug` is the
    // CMS page the "learn more" link resolves to when it is published.
    'products' => [
        ['key' => 'fund',     'number' => '01', 'image' => 'product-fund',     'image_type' => 'jpg', 'slug' => 'paydar-fund'],
        ['key' => 'exchange', 'number' => '02', 'image' => 'product-exchange', 'image_type' => 'jpg', 'slug' => 'paydar-exchange'],
        ['key' => 'broker',   'number' => '03', 'image' => 'product-broker',   'image_type' => 'jpg', 'slug' => 'paydar-broker'],
        ['key' => 'ai',       'number' => '04', 'image' => 'product-ai',       'image_type' => 'png', 'slug' => 'paydar-ai'],
        ['key' => 'bot',      'number' => '05', 'image' => 'product-bot',      'image_type' => 'jpg', 'slug' => 'paydar-bot'],
    ],

    // Page slugs behind the section calls to action (null link when unpublished).
    'links' => [
        'about' => 'about',
        'products' => 'products',
        'blog' => 'blog',
        'privacy' => 'privacy',
        'terms' => 'terms',
    ],

    // Latest published articles shown in the blog section.
    'articles_limit' => 4,

    // Reading speed used for the "n min read" meta (Persian prose).
    'reading_words_per_minute' => 200,

];
