for (const gallery of document.querySelectorAll('[data-home-gallery]')) {
    const links = [...gallery.querySelectorAll('[data-gallery-photo]')];
    const dialog = gallery.querySelector('[data-gallery-dialog]');
    if (!links.length || typeof dialog.showModal !== 'function') continue;
    const image = dialog.querySelector('[data-gallery-image]');
    const caption = dialog.querySelector('[data-gallery-caption]');
    const counter = dialog.querySelector('[data-gallery-counter]');
    const error = dialog.querySelector('[data-gallery-error]');
    const previous = dialog.querySelector('[data-gallery-previous]');
    const next = dialog.querySelector('[data-gallery-next]');
    const thumbnails = dialog.querySelector('[data-gallery-thumbnails]');
    let index = 0;
    let opener;
    const show = (position) => {
        index = (position + links.length) % links.length;
        const link = links[index];
        error.hidden = true;
        image.src = link.href;
        image.alt = link.dataset.caption;
        caption.textContent = link.dataset.caption;
        counter.textContent = `${index + 1} / ${links.length}`;
        [...thumbnails.children].forEach((button, number) => button.setAttribute('aria-current', String(number === index)));
    };
    const open = (position, trigger) => {
        opener = trigger;
        show(position);
        dialog.showModal();
    };
    links.forEach((link, position) => {
        link.addEventListener('click', (event) => { event.preventDefault(); open(position, link); });
        const button = document.createElement('button');
        button.type = 'button';
        button.setAttribute('aria-label', link.dataset.caption);
        const thumbnail = document.createElement('img');
        thumbnail.src = link.href;
        thumbnail.alt = '';
        thumbnail.loading = 'lazy';
        button.append(thumbnail);
        button.addEventListener('click', () => show(position));
        thumbnails.append(button);
    });
    const openButton = gallery.querySelector('[data-gallery-open]');
    openButton.hidden = false;
    openButton.addEventListener('click', () => open(0, openButton));
    gallery.classList.add('is-enhanced');
    previous.disabled = next.disabled = links.length < 2;
    previous.addEventListener('click', () => show(index - 1));
    next.addEventListener('click', () => show(index + 1));
    image.addEventListener('error', () => { error.hidden = false; });
    dialog.querySelector('[data-gallery-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            show(index + (event.key === 'ArrowLeft' ? -1 : 1));
        }
    });
    dialog.addEventListener('close', () => { image.removeAttribute('src'); opener?.focus(); });
}
