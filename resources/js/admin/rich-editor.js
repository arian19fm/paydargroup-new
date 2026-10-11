// Rich content editor (vanilla Tiptap, progressive enhancement).
//
//   <div data-rich-editor data-i18n="{…}" [data-upload-url]> … <textarea class="pg-editor__source">
//
// The textarea holds the HTML and is what the form submits; without
// JavaScript it is a plain HTML source field. This adds two modes:
//
// - "ویرایشگر": a visual editor (headings, lists, links, images…) that
//   writes its HTML back into the textarea on every change;
// - "HTML": the textarea itself, for hand-written markup.
//
// Hand-written HTML can hold markup the visual editor does not model
// (tables, classes, figure…). Such content opens in HTML mode, and in
// visual mode the textarea is only overwritten once something is actually
// edited there, so switching modes never loses markup by itself. The
// server sanitises whatever is submitted (App\Support\Content\HtmlContent).
//
// Fetched as its own chunk only on pages with a [data-rich-editor] field.

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';

const RENAMED = { b: 'strong', i: 'em', del: 's', strike: 's' };

// Structural fingerprint used to tell whether the visual editor can hold
// some HTML without changing it: tags (with Tiptap's equivalents),
// sorted attributes and text, whitespace-insensitive.
const fingerprint = (html) => {
    const template = document.createElement('template');
    template.innerHTML = html;

    const walk = (node) => [...node.childNodes].map((child) => {
        if (child.nodeType === Node.TEXT_NODE) {
            return child.nodeValue;
        }
        if (child.nodeType !== Node.ELEMENT_NODE) {
            return '';
        }

        const tag = child.localName;
        // Tiptap wraps list item text in <p>; treat both forms alike.
        if (tag === 'p' && child.parentNode?.localName === 'li') {
            return walk(child);
        }

        const attrs = [...child.attributes].map((a) => `${a.name}="${a.value}"`).sort().join(' ');
        const name = RENAMED[tag] ?? tag;

        return `<${name}${attrs ? ' ' + attrs : ''}>${walk(child)}</${name}>`;
    }).join('');

    return walk(template.content).replace(/\s+/g, ' ').replace(/\s*(<[^>]+>)\s*/g, '$1').trim();
};

// The markup when pasted text is, as a whole, HTML source (optionally in a
// ```html fence); null for ordinary text. Mirrors HtmlContent::pastedSource.
const pastedSource = (text) => {
    const source = text.trim().replace(/^```[a-z]*[ \t]*\r?\n([\s\S]*?)\r?\n?\s*```$/i, '$1').trim();

    return source.startsWith('<') && source.endsWith('>')
        && /<(!doctype|html|body|article|section|div|p|h[1-6]|ul|ol|table|figure|blockquote)[\s>]/i.test(source) ? source : null;
};

// One block per line, so the HTML tab stays readable.
const formatHtml = (html) => html
    .replace(/(<\/(?:p|h[1-6]|ul|ol|li|blockquote|pre)>|<hr>|<(?:ul|ol)>)/g, '$1\n')
    .trim();

const button = (label, title, onClick) => {
    const el = document.createElement('button');
    el.type = 'button';
    el.className = 'btn btn-sm pg-editor__btn';
    el.textContent = label;
    el.title = title;
    el.setAttribute('aria-label', title);
    el.addEventListener('mousedown', (event) => event.preventDefault()); // keep the selection
    el.addEventListener('click', onClick);

    return el;
};

const field = (label, input) => {
    const wrap = document.createElement('label');
    wrap.className = 'pg-editor__field';
    wrap.append(Object.assign(document.createElement('span'), { textContent: label }), input);

    return wrap;
};

const input = (type, attrs = {}) => Object.assign(document.createElement('input'), { type, className: type === 'checkbox' ? 'form-check-input' : 'form-control form-control-sm', ...attrs });

const setup = (root) => {
    const source = root.querySelector('.pg-editor__source');
    if (!source || root.dataset.richEditorReady) {
        return;
    }
    root.dataset.richEditorReady = '1';

    const t = JSON.parse(root.dataset.i18n || '{}');
    const uploadUrl = root.dataset.uploadUrl;

    // ---- Chrome: mode switch, toolbar, inline panel, notice, surface.
    const modes = document.createElement('div');
    modes.className = 'btn-group btn-group-sm pg-editor__modes';
    modes.setAttribute('role', 'group');
    modes.setAttribute('aria-label', t.modes);
    const visualTab = button(t.visual, t.visual, () => setMode('visual'));
    const htmlTab = button(t.html, t.html, () => setMode('html'));
    visualTab.classList.add('btn-outline-secondary');
    htmlTab.classList.add('btn-outline-secondary');
    modes.append(visualTab, htmlTab);

    const visual = document.createElement('div');
    visual.className = 'pg-editor__visual';

    const toolbar = document.createElement('div');
    toolbar.className = 'pg-editor__toolbar';
    toolbar.setAttribute('role', 'toolbar');
    toolbar.setAttribute('aria-label', t.toolbar);

    const panel = document.createElement('div');
    panel.className = 'pg-editor__panel';
    panel.hidden = true;

    const notice = document.createElement('div');
    notice.className = 'alert alert-warning small py-2 mb-0 pg-editor__notice';
    notice.textContent = t.lossy;
    notice.hidden = true;

    const surface = document.createElement('div');
    surface.className = 'pg-editor__surface';

    const pasted = document.createElement('div');
    pasted.className = 'alert alert-info small py-2 mb-2';
    pasted.textContent = t.pasted_html;
    pasted.hidden = true;

    visual.append(toolbar, panel, notice, surface);
    source.before(modes, visual, pasted);

    // ---- Editor.
    let mode = 'visual';
    let editedVisually = false;

    const editor = new Editor({
        element: surface,
        extensions: [
            StarterKit.configure({
                link: { openOnClick: false, autolink: true, HTMLAttributes: { target: null, rel: null } },
            }),
            Image,
        ],
        content: source.value,
        editorProps: {
            attributes: {
                class: 'pg-content pg-editor__content',
                role: 'textbox',
                'aria-multiline': 'true',
                'aria-label': root.querySelector('label')?.textContent.trim() ?? '',
            },
            handlePaste: (view, event) => {
                const html = pastedSource(event.clipboardData?.getData('text/plain') ?? '');
                if (html === null) {
                    return false;
                }
                event.preventDefault();
                if (editor.state.doc.textContent.trim() === '') {
                    // A whole article: keep every tag it has (HTML mode
                    // when the visual editor cannot hold them all).
                    source.value = html;
                    source.dispatchEvent(new Event('input', { bubbles: true }));
                    editor.commands.setContent(html, { emitUpdate: false });
                    editedVisually = false;
                    setMode(lossy() ? 'html' : 'visual');
                    pasted.hidden = mode !== 'html';
                } else {
                    editor.chain().focus().insertContent(html).run();
                }

                return true;
            },
        },
        onUpdate: () => {
            if (mode !== 'visual') {
                return;
            }
            editedVisually = true;
            notice.hidden = true;
            source.value = editor.isEmpty ? '' : formatHtml(editor.getHTML());
            source.dispatchEvent(new Event('input', { bubbles: true }));
        },
        onTransaction: () => refreshToolbar(),
    });

    const lossy = () => source.value.trim() !== '' && fingerprint(source.value) !== fingerprint(editor.getHTML());

    const setMode = (next) => {
        if (next === 'visual' && mode !== 'visual') {
            editor.commands.setContent(source.value, { emitUpdate: false });
            editedVisually = false;
        }
        mode = next;
        visual.hidden = mode !== 'visual';
        source.hidden = mode === 'visual';
        root.classList.toggle('is-html', mode === 'html');
        visualTab.setAttribute('aria-pressed', String(mode === 'visual'));
        htmlTab.setAttribute('aria-pressed', String(mode === 'html'));
        notice.hidden = mode !== 'visual' || editedVisually || !lossy();
        pasted.hidden = true;
        closePanel();
    };

    // ---- Toolbar.
    const run = (fn) => () => fn(editor.chain().focus()).run();
    const tools = [
        ['P', t.paragraph, run((c) => c.setParagraph()), () => editor.isActive('paragraph')],
        ...[2, 3, 4].map((level) => [`H${level}`, t.heading.replace(':level', level), run((c) => c.toggleHeading({ level })), () => editor.isActive('heading', { level })]),
        null,
        ['B', t.bold, run((c) => c.toggleBold()), () => editor.isActive('bold')],
        ['I', t.italic, run((c) => c.toggleItalic()), () => editor.isActive('italic')],
        ['U', t.underline, run((c) => c.toggleUnderline()), () => editor.isActive('underline')],
        ['S', t.strike, run((c) => c.toggleStrike()), () => editor.isActive('strike')],
        null,
        ['•', t.bullet_list, run((c) => c.toggleBulletList()), () => editor.isActive('bulletList')],
        ['1.', t.ordered_list, run((c) => c.toggleOrderedList()), () => editor.isActive('orderedList')],
        ['❝', t.blockquote, run((c) => c.toggleBlockquote()), () => editor.isActive('blockquote')],
        ['</>', t.code_block, run((c) => c.toggleCodeBlock()), () => editor.isActive('codeBlock')],
        ['—', t.rule, run((c) => c.setHorizontalRule())],
        null,
        [t.link, t.link, () => openLinkPanel(), () => editor.isActive('link')],
        [t.image, t.image, () => openImagePanel()],
        null,
        ['↶', t.undo, run((c) => c.undo())],
        ['↷', t.redo, run((c) => c.redo())],
    ];

    const stateful = [];
    tools.forEach((tool) => {
        if (!tool) {
            toolbar.append(Object.assign(document.createElement('span'), { className: 'pg-editor__sep' }));
            return;
        }
        const [label, title, onClick, isActive] = tool;
        const el = button(label, title, onClick);
        toolbar.append(el);
        if (isActive) {
            stateful.push([el, isActive]);
        }
    });

    const refreshToolbar = () => stateful.forEach(([el, isActive]) => {
        const active = isActive();
        el.classList.toggle('is-active', active);
        el.setAttribute('aria-pressed', String(active));
    });

    // ---- Inline panel (link / image forms). Its inputs have no name, so
    // they are never submitted; Enter applies instead of submitting.
    let applyPanel = null;

    const closePanel = () => {
        panel.hidden = true;
        panel.replaceChildren();
        applyPanel = null;
    };

    const openPanel = (children, onApply, extraActions = []) => {
        panel.replaceChildren(...children);
        const actions = document.createElement('div');
        actions.className = 'pg-editor__actions';
        const apply = Object.assign(document.createElement('button'), { type: 'button', className: 'btn btn-primary btn-sm', textContent: t.apply });
        const cancel = Object.assign(document.createElement('button'), { type: 'button', className: 'btn btn-outline-secondary btn-sm', textContent: t.cancel });
        apply.addEventListener('click', () => onApply(apply));
        cancel.addEventListener('click', () => {
            closePanel();
            editor.commands.focus();
        });
        actions.append(apply, ...extraActions, cancel);
        panel.append(actions);
        panel.hidden = false;
        applyPanel = () => onApply(apply);
        panel.querySelector('input:not([type=checkbox]):not([type=file])')?.focus();

        return apply;
    };

    panel.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && event.target.tagName === 'INPUT') {
            event.preventDefault();
            applyPanel?.();
        } else if (event.key === 'Escape') {
            event.preventDefault();
            closePanel();
            editor.commands.focus();
        }
    });

    const openLinkPanel = () => {
        const current = editor.getAttributes('link');
        const url = input('url', { value: current.href ?? '', dir: 'ltr', placeholder: 'https://' });
        const newTab = input('checkbox', { checked: current.target === '_blank' });
        const tabField = document.createElement('label');
        tabField.className = 'form-check pg-editor__check';
        tabField.append(newTab, Object.assign(document.createElement('span'), { className: 'form-check-label', textContent: t.link_new_tab }));

        const remove = Object.assign(document.createElement('button'), { type: 'button', className: 'btn btn-outline-danger btn-sm', textContent: t.remove_link });
        remove.addEventListener('click', () => {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();
            closePanel();
        });

        openPanel([field(t.link_url, url), tabField], () => {
            const href = url.value.trim();
            const attrs = { href, target: newTab.checked ? '_blank' : null };
            const chain = editor.chain().focus().extendMarkRange('link');

            if (href === '') {
                chain.unsetLink().run();
            } else if (editor.state.selection.empty && !editor.isActive('link')) {
                chain.insertContent({ type: 'text', text: href, marks: [{ type: 'link', attrs }] }).run();
            } else {
                chain.setLink(attrs).run();
            }
            closePanel();
        }, current.href ? [remove] : []);
    };

    const openImagePanel = () => {
        const children = [];
        const file = input('file', { accept: 'image/png,image/jpeg,image/webp,image/gif' });
        if (uploadUrl) {
            children.push(field(t.image_file, file));
        }
        const url = input('url', { dir: 'ltr', placeholder: 'https://' });
        const alt = input('text');
        const altField = field(t.image_alt, alt);
        altField.append(Object.assign(document.createElement('small'), { className: 'form-text', textContent: t.image_alt_help }));
        const error = Object.assign(document.createElement('div'), { className: 'text-danger small', hidden: true });
        children.push(field(t.image_url, url), altField, error);

        openPanel(children, async (apply) => {
            error.hidden = true;
            let image = { src: url.value.trim(), alt: alt.value.trim() };

            if (uploadUrl && file.files.length) {
                const body = new FormData();
                body.append('file', file.files[0]);
                body.append('alt_text', image.alt);
                apply.disabled = true;
                apply.textContent = t.uploading;
                try {
                    const response = await fetch(uploadUrl, {
                        method: 'POST',
                        body,
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(data.errors?.file?.[0] ?? data.message ?? t.upload_failed);
                    }
                    image = { src: data.url, alt: image.alt, width: data.width, height: data.height };
                } catch (e) {
                    error.textContent = e.message || t.upload_failed;
                    error.hidden = false;
                    apply.disabled = false;
                    apply.textContent = t.insert;

                    return;
                }
            }

            if (!image.src) {
                return;
            }
            editor.chain().focus().setImage(image).run();
            closePanel();
        }).textContent = t.insert;
    };

    // Hand-written markup the visual editor would change starts in HTML mode.
    setMode(lossy() ? 'html' : 'visual');
};

export function initRichEditors() {
    document.querySelectorAll('[data-rich-editor]').forEach(setup);
}
