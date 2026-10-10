// Live preview for plain-text content fields (vanilla, progressive).
//
//   <textarea data-text-preview="<preview id>"> + <div id="<preview id>">
//
// Mirrors App\Support\Content\PlainText::toHtml(): everything is text,
// a blank line starts a new paragraph, a single line break is kept. The
// server renders the initial preview; this only keeps it in sync while
// typing. Without JavaScript the field still works, the preview is hidden.

const toParagraphs = (text) => {
    const trimmed = text.trim();

    if (trimmed === '') {
        return [];
    }

    return trimmed.split(/(?:\r\n|\r|\n)(?:[ \t]*(?:\r\n|\r|\n))+/);
};

const render = (target, text) => {
    target.replaceChildren(...toParagraphs(text).map((paragraph) => {
        const p = document.createElement('p');
        const lines = paragraph.split(/\r\n|\r|\n/);

        lines.forEach((line, index) => {
            if (index > 0) {
                p.append(document.createElement('br'));
            }

            p.append(line);
        });

        return p;
    }));
};

document.querySelectorAll('[data-text-preview]').forEach((field) => {
    const target = document.getElementById(field.dataset.textPreview);
    const wrapper = target?.closest('.pg-admin-preview');

    if (!target || !wrapper) {
        return;
    }

    const update = () => {
        render(target, field.value);
        wrapper.hidden = target.childElementCount === 0;
    };

    field.addEventListener('input', update);
    update();
});
