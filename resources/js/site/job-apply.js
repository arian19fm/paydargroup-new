// Careers page: application modals (.pg-modal) and the résumé dropzone.
//
// - A modal flagged data-auto-open (the form that was just submitted, with
//   its success or error state rendered server-side) opens on load, so the
//   redirect lands the visitor back where they were.
// - The dropzone shows the chosen file's name and highlights while a file
//   is dragged over it. Without JavaScript the native file input works.
const openOnLoad = document.querySelector('.modal[data-auto-open]');

if (openOnLoad && window.bootstrap?.Modal) {
    window.bootstrap.Modal.getOrCreateInstance(openOnLoad).show();
}

document.querySelectorAll('[data-dropzone]').forEach((zone) => {
    const input = zone.querySelector('input[type="file"]');
    const label = zone.querySelector('[data-dropzone-file]');

    if (!input || !label) {
        return;
    }

    const showFile = () => {
        const file = input.files && input.files[0];
        label.hidden = !file;
        label.textContent = file ? `${file.name} (${Math.max(1, Math.round(file.size / 1024))} KB)` : '';
        zone.classList.toggle('has-file', Boolean(file));
    };

    input.addEventListener('change', showFile);
    ['dragenter', 'dragover'].forEach((type) => zone.addEventListener(type, (event) => {
        event.preventDefault();
        zone.classList.add('is-dragover');
    }));
    ['dragleave', 'drop'].forEach((type) => zone.addEventListener(type, () => zone.classList.remove('is-dragover')));
    zone.addEventListener('drop', (event) => {
        if (event.dataTransfer?.files?.length) {
            event.preventDefault();
            input.files = event.dataTransfer.files;
            showFile();
        }
    });
    showFile();
});
