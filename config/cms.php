<?php

return [

    // Records per page on admin index screens.
    'per_page' => 20,

    // Slug pattern for public content URLs: lowercase ASCII letters, digits
    // and hyphens (Persian slugs can be enabled later by widening this).
    'slug_pattern' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',

    // Slugs that can never be used by a page because they collide with
    // application routes or infrastructure paths.
    'reserved_slugs' => [
        'admin', 'api', 'articles', 'build', 'contact', 'login', 'logout', 'robots.txt',
        'sitemap.xml', 'storage', 'team', 'up', 'vendor',
    ],

    // Page templates selectable in the admin: key => public Blade view that
    // renders the page. A page without a template uses 'default'.
    'page_templates' => [
        'default' => 'site.pages.show',
        'about' => 'site.pages.templates.about',
    ],

    // schema.org types offered in the SEO form.
    'schema_types' => ['WebPage', 'AboutPage', 'ContactPage', 'Article', 'NewsArticle', 'BlogPosting'],

];
