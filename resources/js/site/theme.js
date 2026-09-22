// Light/dark theme switch.
//
// The site is light by default. <html data-bs-theme> is applied before
// first paint by the inline script in layouts/site.blade.php (dark only
// when a stored choice says so); this module wires the [data-theme-toggle]
// buttons, which flip the attribute and persist the choice. The OS colour
// scheme is deliberately not consulted.

const STORAGE_KEY = 'pg-theme';
const root = document.documentElement;
const buttons = document.querySelectorAll('[data-theme-toggle]');

function apply(theme) {
    root.setAttribute('data-bs-theme', theme);
    buttons.forEach((button) => button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false'));

    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch {
        // Private mode or blocked storage: the choice lasts for this page only.
    }
}

if (buttons.length) {
    const current = root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
    buttons.forEach((button) => {
        button.setAttribute('aria-pressed', current === 'dark' ? 'true' : 'false');
        button.hidden = false;
        button.addEventListener('click', () => apply(root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark'));
    });
}
