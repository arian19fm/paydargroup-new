// Floating geometry ([data-float], components/ui/float-shapes): pause the
// CSS drift while a layer is off screen, so a page with several decorated
// sections only ever animates the ones in view. The animation itself is
// CSS (components/_float.scss); this never touches positions.

const layers = document.querySelectorAll('[data-float]');

if (layers.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => entry.target.classList.toggle('is-paused', !entry.isIntersecting));
    }, { rootMargin: '10% 0px' });

    layers.forEach((layer) => observer.observe(layer));
}
