// App download page: the quick-jump chips above the cards.
//
// Without JS they are plain #anchors. This makes the jump dependable on
// phones, where the cards are stacked far below the chips: it scrolls to
// the chosen card itself, lines it up again once the scroll has ended (a
// lazy image or a late font can move the card while the page is on its
// way), moves focus there and marks the card for a moment so it is clear
// which one was picked — on wide screens all cards share one row and the
// page hardly moves.

const chips = document.querySelectorAll('.pg-apps__chip[href^="#"]');
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
let settleTimer;

function align(card, behavior) {
    card.scrollIntoView({ behavior, block: 'start' });
}

function offTarget(card) {
    const margin = parseFloat(getComputedStyle(card).scrollMarginTop) || 0;
    const atEnd = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2;

    return !atEnd && Math.abs(card.getBoundingClientRect().top - margin) > 2;
}

function jump(card) {
    window.clearTimeout(settleTimer);
    document.querySelectorAll('.pg-app-card.is-landed').forEach((other) => other.classList.remove('is-landed'));

    align(card, reduced.matches ? 'auto' : 'smooth');

    let last = window.scrollY;
    let still = 0;

    // "scrollend" is not available everywhere (Safari); poll until the page
    // has stopped, then correct the position if the card is not at the top.
    const settle = () => {
        still = window.scrollY === last ? still + 1 : 0;
        last = window.scrollY;

        if (still < 2) {
            settleTimer = window.setTimeout(settle, 120);

            return;
        }

        if (offTarget(card)) {
            align(card, 'auto');
        }

        card.focus({ preventScroll: true });
        card.classList.add('is-landed');
        settleTimer = window.setTimeout(() => card.classList.remove('is-landed'), 1600);
    };

    settleTimer = window.setTimeout(settle, 120);
}

chips.forEach((chip) => {
    chip.addEventListener('click', (event) => {
        const card = document.getElementById(decodeURIComponent(chip.hash.slice(1)));

        if (!card) {
            return;
        }

        event.preventDefault();
        card.setAttribute('tabindex', '-1');
        window.history.replaceState(null, '', chip.hash);
        jump(card);
    });
});
