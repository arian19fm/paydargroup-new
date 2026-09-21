// Navigation enhancements (vanilla JS, progressive enhancement).
//
// The header navigation works without JavaScript: links are plain anchors and
// the mobile menu is a Bootstrap offcanvas driven by data attributes. This
// module only adds small refinements.

const nav = document.querySelector('[data-site-nav]');

if (nav) {
    // Mark the current page link for assistive technology when the server
    // did not already do so (e.g. cached fragments).
    const current = window.location.pathname.replace(/\/+$/, '') || '/';
    nav.querySelectorAll('a[href]').forEach((link) => {
        const path = new URL(link.href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
        if (path === current && !link.hasAttribute('aria-current')) {
            link.setAttribute('aria-current', 'page');
        }
    });
}
