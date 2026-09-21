// Advisory character counter for SEO fields (vanilla, progressive).
//
//   <input data-char-count="60"> + <span data-char-count-for="<id>">
//
// Shows "n / limit" and flags overflow with a colour; it never blocks
// submission — limits are guidance, not validation.

document.querySelectorAll('[data-char-count]').forEach((field) => {
    const output = document.querySelector(`[data-char-count-for="${field.id}"]`);

    if (!output) {
        return;
    }

    const limit = Number(field.dataset.charCount) || 0;

    const update = () => {
        const length = field.value.length;
        output.textContent = limit ? `${length} / ${limit}` : String(length);
        output.classList.toggle('text-danger', limit > 0 && length > limit);
    };

    field.addEventListener('input', update);
    update();
});
