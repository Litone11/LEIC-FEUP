let hideTimeout = null;
let closeBound = false;

export function showNotificationToast(message) {
    
    const wrapper = document.getElementById('notification-toast');
    const box = wrapper?.querySelector('[data-toast-box]');
    const text = document.getElementById('notification-toast-message');
    const closeBtn = document.getElementById('notification-toast-close');

    if (!wrapper || !box || !text) return;

    text.textContent = message;

    wrapper.classList.remove('hidden');
    box.classList.remove('-translate-y-4', 'opacity-0');
    box.classList.add('translate-y-0', 'opacity-100');

    clearTimeout(hideTimeout);
    hideTimeout = setTimeout(hideToast, 10000);

    if (closeBtn && !closeBound) {
        closeBound = true;
        closeBtn.addEventListener('click', hideToast);
    }
}

function hideToast() {
    const wrapper = document.getElementById('notification-toast');
    const box = wrapper?.querySelector('[data-toast-box]');
    if (!wrapper || !box) return;

    box.classList.add('-translate-y-4', 'opacity-0');
    box.classList.remove('translate-y-0', 'opacity-100');

    setTimeout(() => {
        wrapper.classList.add('hidden');
    }, 300);
}
