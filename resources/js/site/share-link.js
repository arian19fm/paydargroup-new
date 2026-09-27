// "Copy link" share button ([data-copy-link]): with JavaScript it copies
// the article URL and flashes a confirmation; without it, it is a plain
// link to the article itself.
document.querySelectorAll('[data-copy-link]').forEach((button) => {
    button.addEventListener('click', async (event) => {
        if (!navigator.clipboard) {
            return;
        }

        event.preventDefault();

        try {
            await navigator.clipboard.writeText(button.href);
            button.classList.add('is-copied');
            button.setAttribute('data-copied', button.dataset.copiedLabel || '✓');
            setTimeout(() => button.classList.remove('is-copied'), 1600);
        } catch {
            // Clipboard blocked: leave the link as is.
        }
    });
});
