// Hero video box ([data-hero-video], top of the copy column). The video
// has native controls and never autoplays — the visitor starts it, the
// hero image is its cover until then. The only courtesy here: pause it
// when it scrolls out of view, so sound and playback do not run on behind
// the rest of the page. It is not resumed automatically.

const video = document.querySelector('[data-hero-video]');

if (video && 'IntersectionObserver' in window) {
    new IntersectionObserver(([entry]) => {
        if (!entry.isIntersecting && !video.paused) {
            video.pause();
        }
    }, { threshold: 0 }).observe(video);
}
