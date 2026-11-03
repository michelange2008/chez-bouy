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
    showLanguage('fr');
    tabs.addEventListener('click', (event) => {
        const button = event.target.closest('[data-language]');
        if (button) showLanguage(button.dataset.language);
    });
    form.addEventListener('invalid', (event) => {
        const panel = event.target.closest('[data-language-panel]');
        if (panel) showLanguage(panel.dataset.languagePanel);
    }, true);
    const photos = form.querySelector('[data-section-photos]');
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
