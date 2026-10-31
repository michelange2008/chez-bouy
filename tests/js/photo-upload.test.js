import test from 'node:test';
import assert from 'node:assert/strict';

globalThis.document = { querySelectorAll: () => [] };
const { preparePhoto } = await import('../../resources/js/photo-upload.js');
const options = { maxDimension: 1600, maxBytes: 1024 * 1024, quality: 0.8 };

function browser({ width = 4000, height = 3000, fallback = false, oversized = false } = {}) {
    let closed = false;
    const calls = [];
    globalThis.createImageBitmap = async () => ({ width, height, close() { closed = true; } });
    globalThis.document.createElement = () => ({
        width: 0,
        height: 0,
        getContext: () => ({ drawImage() {}, fillRect() {} }),
        toBlob(callback, type, quality) {
            calls.push({ type, quality, width: this.width, height: this.height });
            const resultType = fallback && type === 'image/webp' ? 'image/png' : type;
            const size = oversized && this.width > 1300 ? 1200000 : 100000;
            callback(new Blob([new Uint8Array(size)], { type: resultType }));
        },
    });
    return { calls, closed: () => closed };
}

const input = () => new File([new Uint8Array(4 * 1024 * 1024)], 'original.jpg', { type: 'image/jpeg' });

test('un original de 4 Mo devient un fichier WebP proportionnel sous 1 Mo', async () => {
    const state = browser();
    const result = await preparePhoto(input(), options);
    assert.equal(result.file.type, 'image/webp');
    assert.deepEqual([result.width, result.height], [1600, 1200]);
    assert.ok(result.file.size <= options.maxBytes);
    assert.equal(state.closed(), true);
});

test('les petites images ne sont pas agrandies', async () => {
    browser({ width: 300, height: 600 });
    const result = await preparePhoto(input(), options);
    assert.deepEqual([result.width, result.height], [300, 600]);
});

test('le repli JPEG utilise le vrai format et une extension cohérente', async () => {
    const state = browser({ fallback: true });
    const result = await preparePhoto(input(), options);
    assert.equal(result.file.type, 'image/jpeg');
    assert.equal(result.file.name, 'photo.jpg');
    assert.deepEqual(state.calls.map(call => call.type), ['image/webp', 'image/jpeg']);
});

test('réduit davantage les dimensions si le premier encodage est trop lourd', async () => {
    const state = browser({ oversized: true });
    const result = await preparePhoto(input(), options);
    assert.equal(result.width, 1280);
    assert.ok(result.file.size <= options.maxBytes);
    assert.equal(state.calls.length, 2);
});

test('refuse un format non pris en charge', async () => {
    browser();
    await assert.rejects(preparePhoto(new File(['svg'], 'image.svg', { type: 'image/svg+xml' }), options));
});

test('une erreur de décodage est signalée', async () => {
    globalThis.createImageBitmap = async () => { throw new Error('invalid image'); };
    await assert.rejects(preparePhoto(input(), options), /invalid image/);
});
