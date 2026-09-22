// Drag-to-scroll for horizontal tracks ([data-drag-scroll]), e.g. the blog
// card row on the home page.
//
// Progressive enhancement over a normal overflow-x container: the track
// still scrolls with a trackpad, Shift+wheel, keyboard focus and touch
// (touch and pen are left entirely to the browser — only mouse pointers
// are handled, so nothing is hijacked). Dragging moves scrollLeft
// directly, keeps a little momentum on release, and swallows the click
// that would otherwise fire on the card you dragged over. Works in RTL
// because it only applies deltas to whatever scrollLeft the browser
// reports.

const DRAG_THRESHOLD = 4; // px before a press counts as a drag, not a click
const FRICTION = 0.92;
const MIN_VELOCITY = 0.3;

function enhance(track) {
    let pointerId = null;
    let startX = 0;
    let startScroll = 0;
    let lastX = 0;
    let lastTime = 0;
    let velocity = 0;
    let dragged = false;
    let momentum = null;

    const canScroll = () => track.scrollWidth > track.clientWidth + 1;

    const stopMomentum = () => {
        if (momentum) {
            cancelAnimationFrame(momentum);
            momentum = null;
        }
    };

    const glide = () => {
        if (Math.abs(velocity) < MIN_VELOCITY) {
            momentum = null;
            track.classList.remove('is-dragging');

            return;
        }

        track.scrollLeft -= velocity * 16;
        velocity *= FRICTION;
        momentum = requestAnimationFrame(glide);
    };

    track.addEventListener('pointerdown', (event) => {
        if (event.pointerType !== 'mouse' || event.button !== 0 || !canScroll()) {
            return;
        }

        stopMomentum();
        pointerId = event.pointerId;
        startX = lastX = event.clientX;
        startScroll = track.scrollLeft;
        lastTime = event.timeStamp;
        velocity = 0;
        dragged = false;
    });

    track.addEventListener('pointermove', (event) => {
        if (event.pointerId !== pointerId) {
            return;
        }

        const delta = event.clientX - startX;

        if (!dragged && Math.abs(delta) < DRAG_THRESHOLD) {
            return;
        }

        if (!dragged) {
            dragged = true;
            track.setPointerCapture(pointerId);
            track.classList.add('is-dragging');
        }

        const dt = Math.max(1, event.timeStamp - lastTime);
        velocity = (event.clientX - lastX) / dt; // px per ms
        lastX = event.clientX;
        lastTime = event.timeStamp;

        track.scrollLeft = startScroll - delta;
        event.preventDefault();
    });

    const release = (event) => {
        if (event.pointerId !== pointerId) {
            return;
        }

        pointerId = null;

        if (!dragged) {
            return;
        }

        if (track.hasPointerCapture(event.pointerId)) {
            track.releasePointerCapture(event.pointerId);
        }

        // Keep the class until the glide ends so scroll-snap stays off and
        // the grabbing cursor does not flicker.
        momentum = requestAnimationFrame(glide);
    };

    track.addEventListener('pointerup', release);
    track.addEventListener('pointercancel', release);

    // A press that turned into a drag must not activate the link under it.
    track.addEventListener('click', (event) => {
        if (dragged) {
            event.preventDefault();
            event.stopPropagation();
            dragged = false;
        }
    }, true);

    track.addEventListener('dragstart', (event) => event.preventDefault());
}

document.querySelectorAll('[data-drag-scroll]').forEach(enhance);
