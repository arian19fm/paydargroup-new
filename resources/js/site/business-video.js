// Business page benefits video (.pg-business-video). Without JavaScript
// the native <video controls> works as is; with it the designed play
// button (hidden by default) is shown over the poster and starts playback,
// hiding itself for the rest of the session on that page.
document.querySelectorAll('[data-business-video]').forEach((wrapper) => {
    const video = wrapper.querySelector('video');
    const button = wrapper.querySelector('[data-business-video-play]');

    if (!video || !button) {
        return;
    }

    button.hidden = false;
    wrapper.classList.add('pg-business-video--idle');

    const start = () => {
        wrapper.classList.remove('pg-business-video--idle');
        button.hidden = true;
        video.play().catch(() => {});
    };

    button.addEventListener('click', start);
    video.addEventListener('play', () => {
        wrapper.classList.remove('pg-business-video--idle');
        button.hidden = true;
    });
});
