export function setupModal({
    modalId = 'create-modal',
    openSelector = '#open-modal-btn',
    closeSelector = '[data-close-modal]',
    focusSelector = null,  
    lockScroll = true
} = {}) {

    const modal = document.getElementById(modalId);
    if (!modal) return;

    const openButtons = document.querySelectorAll(openSelector);
    const closeButtons = document.querySelectorAll(closeSelector);
    const focusInput = focusSelector ? modal.querySelector(focusSelector) : null;

    const open = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (lockScroll) document.body.classList.add('overflow-hidden');

        if (focusInput) {
            setTimeout(() => focusInput.focus(), 50);
        }
    };

    const close = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        if (lockScroll) document.body.classList.remove('overflow-hidden');
    };

    // Make available globally
    modal.dataset.modalOpen = "true";
    window[`open_${modalId}`] = open;
    window[`close_${modalId}`] = close;

    openButtons.forEach(btn => btn.addEventListener('click', open));
    closeButtons.forEach(btn => btn.addEventListener('click', close));

    // Close on outside click
    modal.addEventListener('click', e => {
        if (e.target === modal) close();
    });

    // Close on ESC
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') close();
    });
}
