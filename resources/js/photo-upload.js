function encode(canvas, type, quality) {
    return new Promise((resolve, reject) => {
        canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('encode')), type, quality);
    });
}

async function decode(file) {
    if (typeof createImageBitmap === 'function') {
        return createImageBitmap(file, { imageOrientation: 'from-image' });
    }
    const url = URL.createObjectURL(file);
    try {
        const image = new Image();
        image.src = url;
        await image.decode();
        return image;
    } finally {
        URL.revokeObjectURL(url);
    }
}

export async function preparePhoto(file, { maxDimension, maxBytes, quality }) {
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 30 * 1024 * 1024) {
        throw new Error('unsupported');
    }
    const source = await decode(file);
    try {
        const sourceWidth = source.naturalWidth || source.width;
        const sourceHeight = source.naturalHeight || source.height;
        let ratio = Math.min(1, maxDimension / Math.max(sourceWidth, sourceHeight));
        // Each attempt starts from the decoded original, avoiding cumulative resampling.
        for (let attempt = 0; attempt < 8; attempt++) {
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(sourceWidth * ratio));
            canvas.height = Math.max(1, Math.round(sourceHeight * ratio));
            const context = canvas.getContext('2d');
            if (!context) throw new Error('canvas');
            context.drawImage(source, 0, 0, canvas.width, canvas.height);
            let blob = await encode(canvas, 'image/webp', quality);
            if (blob.type !== 'image/webp') {
                // JPEG does not support transparency: use a white background.
                context.globalCompositeOperation = 'destination-over';
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                blob = await encode(canvas, 'image/jpeg', quality);
                if (blob.type !== 'image/jpeg') throw new Error('unsupported');
            }
            if (blob.size <= maxBytes) {
                const extension = blob.type === 'image/webp' ? 'webp' : 'jpg';
                return {
                    file: new File([blob], `photo.${extension}`, { type: blob.type }),
                    originalName: file.name,
                    width: canvas.width,
                    height: canvas.height,
                };
            }
            ratio *= 0.8;
            quality = Math.max(0.6, quality - 0.05);
        }
        throw new Error('too-large');
    } finally {
        source.close?.();
    }
}

for (const form of document.querySelectorAll('[data-photo-upload]')) {
    const input = form.querySelector('[name="image"]');
    const originalName = form.querySelector('[data-original-filename]');
    const status = form.querySelector('[data-photo-status]');
    const preview = form.querySelector('[data-photo-preview]');
    const submit = form.querySelector('[data-photo-submit]');
    const options = JSON.parse(form.dataset.photoUpload);
    let generation = 0;
    let busy = false;
    let failed = false;
    let previewUrl;

    input.addEventListener('change', async () => {
        const current = ++generation;
        const original = input.files[0];
        originalName.value = '';
        failed = false;
        preview.hidden = true;
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        status.classList.remove('error');
        if (!original) {
            busy = false;
            submit.disabled = false;
            status.textContent = '';
            return;
        }
        busy = true;
        submit.disabled = true;
        status.textContent = options.processing;
        try {
            const prepared = await preparePhoto(original, options);
            if (current !== generation) return;
            originalName.value = prepared.originalName;
            const transfer = new DataTransfer();
            transfer.items.add(prepared.file);
            input.files = transfer.files;
            if (input.files[0]?.size !== prepared.file.size) throw new Error('file-assignment');
            previewUrl = URL.createObjectURL(prepared.file);
            preview.src = previewUrl;
            preview.hidden = false;
            status.textContent = options.ready
                .replace(':before', Math.ceil(original.size / 1024))
                .replace(':after', Math.ceil(prepared.file.size / 1024))
                .replace(':width', prepared.width)
                .replace(':height', prepared.height);
        } catch {
            if (current !== generation) return;
            failed = true;
            input.value = '';
            status.classList.add('error');
            status.textContent = options.failed;
        } finally {
            if (current === generation) {
                busy = false;
                submit.disabled = failed;
            }
        }
    });

    form.addEventListener('submit', event => {
        if (busy || failed) event.preventDefault();
    });
}
