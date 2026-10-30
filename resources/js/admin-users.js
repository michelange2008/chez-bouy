// Les modules Vite s'exécutent après l'analyse du document.
const createDialog = document.getElementById('create-user');
const deleteDialog = document.getElementById('delete-user');

if (createDialog && deleteDialog && typeof createDialog.showModal === 'function') {
    document.querySelectorAll('[data-open-user-dialog]').forEach((button) => {
        button.disabled = false;
        button.addEventListener('click', () => createDialog.showModal());
    });

    document.querySelectorAll('[data-close-user-dialog]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog').close());
    });

    const deleteForm = deleteDialog.querySelector('[data-delete-form]');
    const confirmButton = deleteForm.querySelector('[type="submit"]');
    const identity = deleteDialog.querySelector('[data-delete-identity]');

    document.querySelectorAll('[data-delete-user]').forEach((button) => {
        button.disabled = false;
        button.addEventListener('click', () => {
            deleteForm.action = button.dataset.deleteUrl;
            identity.textContent = button.dataset.userEmail;
            confirmButton.disabled = false;
            deleteDialog.showModal();
        });
    });

    deleteDialog.addEventListener('close', () => {
        deleteForm.removeAttribute('action');
        identity.textContent = '';
        confirmButton.disabled = true;
    });

    deleteForm.addEventListener('submit', (event) => {
        if (!deleteForm.hasAttribute('action')) {
            event.preventDefault();
            return;
        }
        confirmButton.disabled = true;
    });

    if (createDialog.dataset.reopen === 'true') {
        createDialog.showModal();
    }
}
