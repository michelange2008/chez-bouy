const rentalForm = document.querySelector('[data-rental-form]');

if (rentalForm) {
    const user = rentalForm.elements.user_id;
    const newUser = rentalForm.querySelector('[data-rental-new-user]');

    function updateUserFields() {
        const addingUser = user.value === 'new';
        newUser.hidden = !addingUser;
        newUser.disabled = !addingUser;
        for (const input of newUser.querySelectorAll('[data-required-for-new]')) {
            input.required = addingUser;
        }
    }

    if (user) {
        updateUserFields();
        user.addEventListener('change', updateUserFields);
    }

    const type = rentalForm.elements.type;
    const amount = rentalForm.elements.amount;

    function updateAmount() {
        const paid = type.value === 'rental';
        amount.closest('[data-rental-amount]').hidden = !paid;
        amount.disabled = !paid;
        amount.required = paid;
    }

    updateAmount();
    type.addEventListener('change', updateAmount);
}

for (const dateInput of document.querySelectorAll('[data-rental-payment] input[type="date"]')) {
    dateInput.addEventListener('click', () => {
        if (typeof dateInput.showPicker !== 'function') {
            return;
        }

        try {
            dateInput.showPicker();
        } catch {
            // Let the browser handle the click when programmatic opening is blocked.
        }
    });
}

for (const form of document.querySelectorAll('[data-rental-checklist], [data-rental-payment]')) {
    const button = form.querySelector('button');
    const payments = form.closest('[data-rental-payments]');
    const saveButtons = payments?.querySelectorAll('[data-rental-payment] button[type="submit"]') ?? [button];
    const value = form.elements.value;
    const error = form.querySelector('[role="alert"]');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (button.disabled) {
            return;
        }

        const data = new FormData(form);
        const inputs = form.querySelectorAll('input:not([type="hidden"])');
        saveButtons.forEach((saveButton) => { saveButton.disabled = true; });
        inputs.forEach((input) => { input.disabled = true; });
        error.hidden = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: data,
            });

            if (!response.ok) {
                throw new Error('Rental update failed');
            }

            const result = await response.json();
            if (value) {
                button.textContent = result.label;
                button.dataset.checked = String(result.value);
                value.value = result.value ? '0' : '1';

                if (form.dataset.rentalChecklist === 'does_cleaning') {
                    form.closest('[data-rental-card]').querySelector('[data-rental-concierge]').hidden = result.value;
                }

                const codeReminder = form.parentElement.nextElementSibling;
                if (codeReminder?.matches('[data-code-reminder]')) {
                    codeReminder.hidden = result.value;
                }
            } else {
                const details = form.closest('details');
                const payment = result.payment;
                details.closest('[data-payment-state]').dataset.paymentState = payment.complete ? 'paid' : 'pending';
                details.querySelector('[data-payment-date]').textContent = payment.date;
                details.querySelector('[data-payment-amount]').textContent = payment.amount;

                const total = payments.querySelector('[data-payment-total]');
                const balance = total.closest('[data-payment-state]');
                balance.dataset.paymentState = { matched: 'paid', mismatch: 'mismatch' }[result.total] ?? 'pending';
                total.querySelector('[data-total-matched]').hidden = result.total !== 'matched';
                total.querySelector('[data-total-mismatch]').hidden = result.total !== 'mismatch';
                details.open = false;
            }
        } catch {
            error.hidden = false;
        } finally {
            inputs.forEach((input) => { input.disabled = false; });
            saveButtons.forEach((saveButton) => { saveButton.disabled = false; });
        }
    });
}
