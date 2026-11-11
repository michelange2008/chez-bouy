const fullDay = document.getElementById('event-fullday');

fullDay?.addEventListener('change', () => {
    const form = fullDay.form;
    for (const input of form.querySelectorAll('[data-event-date]')) {
        const [date, time] = input.value.split('T');
        if (time) {
            input.dataset.time = time;
        }
        input.type = fullDay.checked ? 'date' : 'datetime-local';
        if (date) {
            input.value = fullDay.checked ? date : date + 'T' + (input.dataset.time || '00:00');
        }
        input.required = input.name === 'start' || !fullDay.checked;
    }
    form.querySelector('[data-full-day-label]').hidden = !fullDay.checked;
});

const colorPicker = document.getElementById('event-color');
if (colorPicker) {
    const resetColor = colorPicker.form.querySelector('[data-reset-color]');
    function useCustomColor(custom) {
        colorPicker.name = custom ? 'color' : '';
        resetColor.hidden = !custom;
    }

    useCustomColor(colorPicker.dataset.customColor === '1');
    colorPicker.addEventListener('input', () => useCustomColor(true));
    resetColor.addEventListener('click', () => {
        colorPicker.value = colorPicker.dataset.defaultColor;
        useCustomColor(false);
        colorPicker.focus();
    });
}
