document.querySelectorAll('[data-section-form]').forEach((form) => {
    const tabs = form.querySelector('[data-language-tabs]');
    const panels = [...form.querySelectorAll('[data-language-panel]')];
    const showLanguage = (locale) => {
        panels.forEach((panel) => { panel.hidden = panel.dataset.languagePanel !== locale; });
        tabs.querySelectorAll('[data-language]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.language === locale));
        });
    };
    tabs.hidden = false;
    showLanguage(['fr', 'en', 'it'].includes(form.dataset.activeLanguage) ? form.dataset.activeLanguage : 'fr');
    tabs.addEventListener('click', (event) => {
        const button = event.target.closest('[data-language]');
        if (button) showLanguage(button.dataset.language);
    });
    form.addEventListener('invalid', (event) => {
        const panel = event.target.closest('[data-language-panel]');
        if (panel) showLanguage(panel.dataset.languagePanel);
    }, true);
    form.querySelectorAll('[data-section-photos]').forEach((photos) => {
        const updateButtons = () => {
            [...photos.children].forEach((row, index) => {
                const up = row.querySelector('[data-photo-move="up"]');
                const down = row.querySelector('[data-photo-move="down"]');
                if (up) up.disabled = index === 0;
                if (down) down.disabled = index === photos.children.length - 1;
            });
        };
        photos.querySelectorAll('[data-photo-controls]').forEach((controls) => { controls.hidden = false; });
        updateButtons();
        photos.addEventListener('click', (event) => {
            const button = event.target.closest('[data-photo-move]');
            if (!button) return;
            const row = button.closest('[data-section-photo]');
            if (button.dataset.photoMove === 'up' && row.previousElementSibling) {
                photos.insertBefore(row, row.previousElementSibling);
            } else if (button.dataset.photoMove === 'down' && row.nextElementSibling) {
                photos.insertBefore(row.nextElementSibling, row);
            }
            updateButtons();
            button.focus();
        });
    });
});

export function formatMarkdown(text, start, end, format) {
    const selected = text.slice(start, end);
    if (format === 'heading' || format === 'quote') {
        const lineStart = text.lastIndexOf('\n', start - 1) + 1;
        const nextBreak = text.indexOf('\n', end);
        const lineEnd = nextBreak === -1 ? text.length : nextBreak;
        const prefix = format === 'heading' ? '## ' : '> ';
        return { start: lineStart, end: lineEnd, replacement: text.slice(lineStart, lineEnd).split('\n').map((line) => prefix + line).join('\n') };
    }
    if (format === 'link') return { start, end, replacement: `[${selected}](https://)` };
    const marker = format === 'bold' ? '**' : '*';
    return { start, end, replacement: marker + selected + marker };
}

document.querySelectorAll('[data-section-form]').forEach((form) => {
    form.querySelectorAll('[data-language-panel]').forEach((panel) => {
        const toolbar = panel.querySelector('[data-markdown-toolbar]');
        const textarea = panel.querySelector('textarea');
        const output = panel.querySelector('[data-markdown-output]');
        const status = panel.querySelector('[data-preview-status]');
        const previewButton = panel.querySelector('[data-markdown-preview]');
        let revision = 0;
        toolbar.hidden = false;
        textarea.addEventListener('input', () => {
            revision += 1;
            if (!output.hidden || previewButton.disabled) {
                output.hidden = true;
                status.textContent = status.dataset.stale;
            }
        });
        toolbar.addEventListener('click', async (event) => {
            const formatButton = event.target.closest('[data-markdown-format]');
            if (formatButton) {
                const edit = formatMarkdown(textarea.value, textarea.selectionStart, textarea.selectionEnd, formatButton.dataset.markdownFormat);
                textarea.setRangeText(edit.replacement, edit.start, edit.end, 'select');
                const format = formatButton.dataset.markdownFormat;
                if (format === 'bold' || format === 'italic') {
                    const markerLength = format === 'bold' ? 2 : 1;
                    textarea.setSelectionRange(edit.start + markerLength, edit.start + edit.replacement.length - markerLength);
                } else if (format === 'link') {
                    const urlStart = edit.start + edit.replacement.lastIndexOf('https://');
                    textarea.setSelectionRange(urlStart, urlStart + 8);
                }
                textarea.focus();
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                return;
            }
            if (!event.target.closest('[data-markdown-preview]')) return;
            const currentRevision = revision;
            previewButton.disabled = true;
            output.hidden = true;
            status.textContent = status.dataset.loading;
            try {
                const response = await fetch(form.dataset.previewUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                    },
                    body: JSON.stringify({ body: textarea.value }),
                });
                if (!response.ok) throw new Error('Preview failed');
                const data = await response.json();
                if (revision !== currentRevision) return;
                // Only HTML rendered by the shared, secured server renderer is inserted here.
                output.innerHTML = data.html;
                output.hidden = false;
                status.textContent = status.dataset.ready;
            } catch {
                if (revision === currentRevision) status.textContent = status.dataset.error;
            } finally {
                previewButton.disabled = false;
            }
        });
    });
});

const photoDialog = document.querySelector('[data-photo-dialog]');
if (photoDialog && typeof photoDialog.showModal === 'function') {
    const image = photoDialog.querySelector('[data-photo-dialog-image]');
    const error = photoDialog.querySelector('[data-photo-dialog-error]');
    let opener;
    document.querySelectorAll('[data-photo-enlarge]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            opener = link;
            photoDialog.querySelector('[data-photo-dialog-title]').textContent = link.dataset.photoTitle;
            photoDialog.querySelector('[data-photo-dialog-filename]').textContent = link.dataset.photoFilename ? `(${link.dataset.photoFilename})` : '';
            photoDialog.querySelector('[data-photo-dialog-original]').href = link.href;
            error.hidden = true;
            image.alt = link.dataset.photoTitle;
            image.src = link.href;
            photoDialog.showModal();
        });
    });
    image.addEventListener('error', () => { error.hidden = false; });
    photoDialog.querySelector('[data-photo-dialog-close]').addEventListener('click', () => photoDialog.close());
    photoDialog.addEventListener('click', (event) => {
        const bounds = photoDialog.getBoundingClientRect();
        if (event.target === photoDialog && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) photoDialog.close();
    });
    photoDialog.addEventListener('close', () => {
        image.removeAttribute('src');
        opener?.focus();
    });
}
