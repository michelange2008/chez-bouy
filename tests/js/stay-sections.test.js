import test from 'node:test';
import assert from 'node:assert/strict';

globalThis.document = { querySelectorAll: () => [] };
const { formatMarkdown } = await import('../../resources/js/stay-sections.js');

test('formats only the selected text and preserves surrounding content', () => {
    const text = 'Avant important après';
    const edit = formatMarkdown(text, 6, 15, 'bold');
    assert.equal(text.slice(0, edit.start) + edit.replacement + text.slice(edit.end), 'Avant **important** après');
    assert.equal(formatMarkdown('mot', 0, 3, 'italic').replacement, '*mot*');
    assert.equal(formatMarkdown('plan', 0, 4, 'link').replacement, '[plan](https://)');
});

test('formats complete lines for headings and multiline quotes', () => {
    assert.deepEqual(formatMarkdown('Avant\nPorte\nClé\nAprès', 7, 14, 'quote'), {
        start: 6, end: 15, replacement: '> Porte\n> Clé',
    });
    assert.equal(formatMarkdown('Accès', 2, 2, 'heading').replacement, '## Accès');
    assert.equal(formatMarkdown('', 0, 0, 'bold').replacement, '****');
});
