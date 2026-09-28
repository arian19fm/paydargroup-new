<?php

/*
|--------------------------------------------------------------------------
| Home page structure
|--------------------------------------------------------------------------
|
| Non-textual structure of the public home page: the CMS page slugs the
| calls to action point at. Businesses (the products section) are managed
| in the admin. Copy lives in lang/{locale}/home.php; articles, menus and settings
| come from the CMS. A CTA whose page is not published renders without a
| link, so nothing here can produce a dead link.
|
*/

return [

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
