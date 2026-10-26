// Tap-safe links ([data-tap-link]): links on surfaces that move while the
// page scrolls — the home showcase frames, which slide under the finger in
// pinned mode and sit in a sticky panel otherwise.
//
// The browser already drops a tap that turned into a scroll. This also
// drops a tap that lands while the page is still moving (a touch meant to
// stop a fling, or a card that slid under the finger between press and
// release), so an image is only opened by a clean, still tap. Mouse and
// keyboard are left alone; the links stay ordinary anchors without JS.

const SLOP = 10; // px the finger may travel and still count as a tap
const SETTLE = 150; // ms since the last scroll event for the page to count as still
const MAX_PRESS = 700; // ms; longer is a hold (menu, selection), not a tap

let lastScroll = -Infinity;

document.addEventListener('scroll', (event) => {
    lastScroll = event.timeStamp;
}, { capture: true, passive: true });

function enhance(link) {
    let press = null;

    link.addEventListener('pointerdown', (event) => {
        if (event.pointerType === 'mouse') {
            press = null;

            return;
        }

        const box = link.getBoundingClientRect();

        press = {
            x: event.clientX,
            y: event.clientY,
            top: box.top,
            left: box.left,
            time: event.timeStamp,
            // Pressed while the page was still scrolling (momentum).
            moving: event.timeStamp - lastScroll < SETTLE,
        };
    });

    link.addEventListener('pointercancel', () => {
        press = null;
    });

    link.addEventListener('click', (event) => {
        const start = press;
        press = null;

        // Keyboard activation, or a mouse: nothing to filter.
        if (!start) {
            return;
        }

        const box = link.getBoundingClientRect();
        const travelled = Math.hypot(event.clientX - start.x, event.clientY - start.y) > SLOP;
        const shifted = Math.abs(box.top - start.top) > 2 || Math.abs(box.left - start.left) > 2;
        const held = event.timeStamp - start.time > MAX_PRESS;
        const scrolled = event.timeStamp - lastScroll < SETTLE;

        if (start.moving || travelled || shifted || held || scrolled) {
            event.preventDefault();
        }
    });
}

document.querySelectorAll('a[data-tap-link]').forEach(enhance);
