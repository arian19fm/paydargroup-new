// Article reading aids (site/articles/show): the table-of-contents entry of
// the section being read is highlighted ([data-article-toc]), and a thin bar
// ([data-article-progress]) shows how much of the body has been read.
// Without JavaScript the contents list is a plain list of anchors.

const body = document.querySelector('[data-article-body]');
const toc = document.querySelector('[data-article-toc]');
const progress = document.querySelector('[data-article-progress]');

if (body && toc) {
    const links = new Map(
        [...toc.querySelectorAll('a[href^="#"]')]
            .map((link) => [document.getElementById(decodeURIComponent(link.hash.slice(1))), link])
            .filter(([heading]) => heading),
    );

    const update = () => {
        // The last heading above a line a third of the way down the viewport.
        let current = null;
        links.forEach((link, heading) => {
            if (heading.getBoundingClientRect().top < window.innerHeight / 3) {
                current = link;
            }
        });
        links.forEach((link) => {
            link.classList.toggle('is-active', link === current);
            if (link === current) {
                link.setAttribute('aria-current', 'location');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    window.addEventListener('scroll', update, { passive: true });
    update();
}

if (body && progress) {
    const update = () => {
        const rect = body.getBoundingClientRect();
        const total = rect.height - window.innerHeight / 2;
        const read = Math.min(1, Math.max(0, (window.innerHeight / 2 - rect.top) / Math.max(1, total)));
        progress.style.transform = `scaleX(${read})`;
    };

    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
}
