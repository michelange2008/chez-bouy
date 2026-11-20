const rentalForm = document.querySelector('[data-rental-form]');

if (rentalForm) {
    const user = rentalForm.elements.user_id;
    const newUser = rentalForm.querySelector('[data-rental-new-user]');
    const type = rentalForm.elements.type;
    const amount = rentalForm.elements.amount;

    function updateUserFields() {
        const addingUser = user.value === 'new';
        newUser.hidden = !addingUser;
        newUser.disabled = !addingUser;
        for (const input of newUser.querySelectorAll('[data-required-for-new]')) {
            input.required = addingUser;
        }
    }

    function updateAmount() {
        const paid = type.value === 'rental';
        amount.closest('[data-rental-amount]').hidden = !paid;
        amount.disabled = !paid;
        amount.required = paid;
    }

    if (user) {
        updateUserFields();
        user.addEventListener('change', updateUserFields);
    }
    updateAmount();
    type.addEventListener('change', updateAmount);
}
