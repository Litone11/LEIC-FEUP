const DEFAULT_DURATION = 5000;

function initToast(wrapper, { duration = DEFAULT_DURATION, removeOnHide = false } = {}) {
    if (!wrapper) return;

    const box = wrapper.querySelector('[data-flash-box]');
    const closeBtn = wrapper.querySelector('[data-flash-close]');
    if (!box) return;

    requestAnimationFrame(() => {
        box.classList.remove('opacity-0', '-translate-y-4');
        box.classList.add('opacity-100', 'translate-y-0');
    });

    const hide = () => {
        box.classList.add('opacity-0', '-translate-y-4');
        box.classList.remove('opacity-100', 'translate-y-0');

        setTimeout(() => {
            if (removeOnHide) {
                wrapper.remove();
            } else {
                wrapper.classList.add('hidden');
            }
        }, 300);
    };

    closeBtn?.addEventListener('click', hide);
    setTimeout(hide, duration);
}

function buildToastMarkup({ message, type }) {
    const isSuccess = type !== 'error';
    const colorClass = isSuccess
        ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
        : 'bg-rose-50 border-rose-200 text-rose-800';
    const iconClass = isSuccess
        ? 'bi-check-circle-fill text-emerald-600'
        : 'bi-x-circle-fill text-rose-600';

    return `
        <div class="px-4 sm:px-8 lg:px-12">
            <div data-flash-box
                 class="flex items-center justify-between rounded-2xl border px-4 py-3 text-sm shadow-sm
                        transform transition-[opacity,transform] duration-300 ease-out
                        -translate-y-4 opacity-0 ${colorClass}">
                <div class="flex items-center gap-3">
                    <i class="bi ${iconClass}"></i>
                    <span>${message}</span>
                </div>
                <button type="button"
                        data-flash-close
                        class="text-xs opacity-70 hover:opacity-100">
                    Fechar
                </button>
            </div>
        </div>
    `;
}

export function setupFlashToast() {
    const serverToast = document.getElementById('flash-toast');
    if (serverToast) {
        initToast(serverToast);
    }

    window.showFlashToast = function ({ message, type = 'success', duration = DEFAULT_DURATION } = {}) {
        if (!message) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'fixed top-0 inset-x-0 z-50';
        wrapper.innerHTML = buildToastMarkup({ message, type });

        document.body.appendChild(wrapper);
        initToast(wrapper, { duration, removeOnHide: true });
    };
}
