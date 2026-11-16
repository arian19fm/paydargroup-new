// Careers benefits ([data-benefits-cycle], site/careers): the highlighted
// card moves to the next one in a loop.
//
// The server marks the first card as the lead (.pg-benefit-card--lead), so
// without JavaScript that card stays highlighted. With it, the list gets
// .is-cycling and the active card shows a progress hairline (CSS animation,
// --pg-cycle long); when that animation ends the next card takes over. So
// pausing is just pausing the animation (.is-paused): while the list is off
// screen or the tab is hidden, while the mouse is over it, or while focus is
// inside it. Under reduced motion nothing cycles.

const list = document.querySelector('[data-benefits-cycle]');
const DURATION = 3200;

if (list && list.children.length > 1) {
    const cards = Array.from(list.children);
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

    let index = 0;
    let onScreen = false;
    let held = false;

    const show = (next) => {
        cards[index].classList.remove('is-active');
        index = next;
        cards[index].classList.add('is-active');
    };

    const update = () => {
        list.classList.toggle('is-cycling', !reduced.matches);
        list.classList.toggle('is-paused', !onScreen || document.hidden || held);
    };

    list.style.setProperty('--pg-cycle', `${DURATION}ms`);
    cards[0].classList.remove('pg-benefit-card--lead');
    cards[0].classList.add('is-active');

    list.addEventListener('animationend', (event) => {
        if (event.animationName === 'pg-benefit-progress' && event.target === cards[index]) {
            show((index + 1) % cards.length);
        }
    });

    new IntersectionObserver((entries) => {
        onScreen = entries[0].isIntersecting;
        update();
    }, { threshold: 0.25 }).observe(list);

    const hold = (value) => {
        held = value;
        update();
    };

    // Mouse only: a tap fires pointerenter but often no pointerleave.
    list.addEventListener('pointerenter', (event) => event.pointerType === 'mouse' && hold(true));
    list.addEventListener('pointerleave', (event) => event.pointerType === 'mouse' && hold(false));
    list.addEventListener('focusin', () => hold(true));
    list.addEventListener('focusout', (event) => {
        if (!list.contains(event.relatedTarget)) {
            hold(false);
        }
    });
    reduced.addEventListener('change', update);
    document.addEventListener('visibilitychange', update);
    update();
}
