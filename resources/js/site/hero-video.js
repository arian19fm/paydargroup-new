// Hero background video ([data-hero-video]) — small courtesies on top of
// the native autoplay/muted/loop attributes:
//   • never play for visitors who prefer reduced motion (CSS also hides it),
//   • pause while the hero is scrolled out of view, resume when it returns,
//   • if autoplay is blocked (battery saver, data saver) just leave the
//     poster photo — no play buttons, no retries.

const video = document.querySelector('[data-hero-video]');

if (video) {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

    const sync = (visible) => {
        if (reduced.matches || !visible) {
            video.pause();

            return;
        }

        const attempt = video.play();

        if (attempt && typeof attempt.catch === 'function') {
            attempt.catch(() => {});
        }
    };

    reduced.addEventListener('change', () => sync(true));

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => sync(entry.isIntersecting), { threshold: 0.1 }).observe(video);
    } else {
        sync(true);
    }
}
