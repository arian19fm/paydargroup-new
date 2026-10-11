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
        'admin', 'api', 'articles', 'build', 'businesses', 'careers', 'contact', 'login', 'logout', 'robots.txt',
        'sitemap.xml', 'sitemap-news.xml', 'sitemap.xsl', 'sitemaps', 'storage', 'team', 'up', 'vendor',
    ],

    // The site's pages are fixed: they cannot be created or deleted in the
    // admin (only edited), and their slug and template are locked. These
    // are guaranteed to exist by the 2026_09_28_140000_ensure_fixed_pages
    // migration; any other page already in the database stays as it is.
    'fixed_pages' => [
        'about' => ['template' => 'about'],
        'privacy' => ['template' => null],
        'terms' => ['template' => null],
    ],

    // Page templates: key => public Blade view that renders the page. A page
    // without a template uses 'default'.
    'page_templates' => [
        'default' => 'site.pages.show',
        'about' => 'site.pages.templates.about',
    ],

    // Images used inside content (article covers, images inserted in the
    // editor) are stored as WebP scaled down to this width — the article
    // column — at this quality (App\Support\Media\WebpImage).
    'content_image' => [
        'width' => 750,
        'quality' => 82,
    ],

    // schema.org types offered in the SEO form.
    'schema_types' => ['WebPage', 'AboutPage', 'ContactPage', 'Article', 'NewsArticle', 'BlogPosting'],

];
