document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.querySelector('[data-auth-modal]');
    if (!trigger || typeof bootstrap === 'undefined') return;

    const modalId = trigger.dataset.authModal === 'register' ? 'registerModal' : 'loginModal';
    const modalEl = document.getElementById(modalId);

    if (modalEl) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
});