// Hero particle field ([data-hero-particles] canvas inside .pg-hero).
//
// A light "network" of drifting points joined by faint lines when they
// come close, in the hero's blue; the pointer nudges nearby points aside
// and they ease back to their drift. Progressive enhancement over the
// photo: the canvas is empty without JavaScript, it is never created when
// the visitor prefers reduced motion, it pauses while the hero is off
// screen or the tab is hidden, and it draws at most 2× DPR. Pointer
// listeners are passive and the canvas ignores pointer events, so
// scrolling and the hero CTA are untouched.

const COLOUR = [120, 190, 255]; // --pg-globe-glow family (hero accent)
const DENSITY = 1 / 16000; // particles per CSS pixel² (≈ 36 on a 375×609 phone)
const MIN = 28;
const MAX = 110;
const LINK = 120; // px: draw a line between points closer than this
const POINTER_RADIUS = 150; // px: how far the pointer is felt
const POINTER_FORCE = 0.9;

function createField(canvas) {
    const hero = canvas.closest('.pg-hero') || canvas.parentElement;
    const context = canvas.getContext('2d', { alpha: true });

    if (!context || !hero) {
        return null;
    }

    let width = 0;
    let height = 0;
    let dpr = 1;
    let particles = [];
    let frame = 0;
    let running = false;
    let visible = true;
    const pointer = { x: -1e4, y: -1e4, active: false };

    const random = (min, max) => min + Math.random() * (max - min);

    const spawn = (count) => Array.from({ length: count }, () => {
        const angle = random(0, Math.PI * 2);
        const speed = random(0.08, 0.26);

        return {
            x: random(0, width),
            y: random(0, height),
            vx: Math.cos(angle) * speed,
            vy: Math.sin(angle) * speed,
            bx: Math.cos(angle) * speed, // base drift the point returns to
            by: Math.sin(angle) * speed,
            r: random(0.9, 2.1),
            a: random(0.25, 0.8),
            phase: random(0, Math.PI * 2),
        };
    });

    const resize = () => {
        const rect = hero.getBoundingClientRect();
        width = Math.max(1, Math.round(rect.width));
        height = Math.max(1, Math.round(rect.height));
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;
        context.setTransform(dpr, 0, 0, dpr, 0, 0);

        const count = Math.round(Math.min(MAX, Math.max(MIN, width * height * DENSITY)));

        if (particles.length > count) {
            particles.length = count;
        } else if (particles.length < count) {
            particles = particles.concat(spawn(count - particles.length));
        }
    };

    const step = (now) => {
        const t = now / 1000;
        context.clearRect(0, 0, width, height);

        for (const p of particles) {
            // Pointer: a gentle push away, strongest at the centre.
            if (pointer.active) {
                const dx = p.x - pointer.x;
                const dy = p.y - pointer.y;
                const d2 = dx * dx + dy * dy;

                if (d2 < POINTER_RADIUS * POINTER_RADIUS && d2 > 0.01) {
                    const d = Math.sqrt(d2);
                    const force = ((POINTER_RADIUS - d) / POINTER_RADIUS) * POINTER_FORCE;
                    p.vx += (dx / d) * force * 0.12;
                    p.vy += (dy / d) * force * 0.12;
                }
            }

            // Ease the velocity back to the base drift, then move.
            p.vx += (p.bx - p.vx) * 0.03;
            p.vy += (p.by - p.vy) * 0.03;
            p.x += p.vx;
            p.y += p.vy;

            // Wrap around the edges so the field never thins out.
            if (p.x < -10) p.x = width + 10;
            if (p.x > width + 10) p.x = -10;
            if (p.y < -10) p.y = height + 10;
            if (p.y > height + 10) p.y = -10;
        }

        // Links first (under the points).
        context.lineWidth = 0.7;

        for (let i = 0; i < particles.length; i++) {
            const a = particles[i];

            for (let j = i + 1; j < particles.length; j++) {
                const b = particles[j];
                const dx = a.x - b.x;
                const dy = a.y - b.y;
                const d2 = dx * dx + dy * dy;

                if (d2 < LINK * LINK) {
                    const alpha = (1 - Math.sqrt(d2) / LINK) * 0.28;
                    context.strokeStyle = `rgba(${COLOUR}, ${alpha})`;
                    context.beginPath();
                    context.moveTo(a.x, a.y);
                    context.lineTo(b.x, b.y);
                    context.stroke();
                }
            }
        }

        for (const p of particles) {
            const twinkle = 0.75 + 0.25 * Math.sin(t * 1.3 + p.phase);
            context.fillStyle = `rgba(${COLOUR}, ${p.a * twinkle})`;
            context.beginPath();
            context.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            context.fill();
        }

        frame = running ? window.requestAnimationFrame(step) : 0;
    };

    const start = () => {
        if (running || !visible || document.hidden) {
            return;
        }

        running = true;
        frame = window.requestAnimationFrame(step);
    };

    const stop = () => {
        running = false;

        if (frame) {
            window.cancelAnimationFrame(frame);
            frame = 0;
        }
    };

    const onPointerMove = (event) => {
        const rect = canvas.getBoundingClientRect();
        pointer.x = event.clientX - rect.left;
        pointer.y = event.clientY - rect.top;
        pointer.active = true;
    };

    const onPointerLeave = () => {
        pointer.active = false;
    };

    hero.addEventListener('pointermove', onPointerMove, { passive: true });
    hero.addEventListener('pointerleave', onPointerLeave, { passive: true });
    hero.addEventListener('pointercancel', onPointerLeave, { passive: true });

    if ('ResizeObserver' in window) {
        new ResizeObserver(resize).observe(hero);
    } else {
        window.addEventListener('resize', resize, { passive: true });
    }

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            visible = entry.isIntersecting;
            visible ? start() : stop();
        }, { threshold: 0.05 }).observe(hero);
    }

    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));

    resize();
    start();

    return {
        destroy() {
            stop();
            context.clearRect(0, 0, width, height);
            hero.removeEventListener('pointermove', onPointerMove);
            hero.removeEventListener('pointerleave', onPointerLeave);
            hero.removeEventListener('pointercancel', onPointerLeave);
        },
    };
}

const canvas = document.querySelector('[data-hero-particles]');

if (canvas) {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    let field = null;

    const sync = () => {
        if (reduced.matches) {
            field?.destroy();
            field = null;
        } else if (!field) {
            field = createField(canvas);
        }
    };

    reduced.addEventListener('change', sync);
    sync();
}
