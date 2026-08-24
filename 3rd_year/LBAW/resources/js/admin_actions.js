const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content;

const showToast = (message, type = 'success') => {
    if (window.showFlashToast) {
        window.showFlashToast({ message, type });
    } else {
        console[type === 'error' ? 'error' : 'log'](message);
    }
};

export function setupAdminActions() {
    const forms = document.querySelectorAll('form[data-admin-action]');
    if (!forms.length) return;

    forms.forEach(form => {
        form.addEventListener('submit', async event => {
            if (!window.fetch) return;

            event.preventDefault();

            const successMessage = form.dataset.successMessage || 'Operação concluída.';
            const errorMessage = form.dataset.errorMessage || 'Não foi possível concluir a operação.';
            const shouldReload = form.dataset.reload !== 'false';

            const formData = new FormData(form);
            const methodOverride = (formData.get('_method') || form.method || 'POST').toUpperCase();
            const fetchMethod = methodOverride === 'GET' ? 'GET' : 'POST';

            try {
                const response = await fetch(form.action, {
                    method: fetchMethod,
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken() || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: fetchMethod === 'GET' ? null : formData,
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.success === false) {
                    throw new Error(payload.message || errorMessage);
                }

                showToast(payload.message || successMessage, 'success');

                if (shouldReload) {
                    setTimeout(() => window.location.reload(), 1200);
                }
            } catch (error) {
                console.error(error);
                showToast(error.message || errorMessage, 'error');
            }
        });
    });
}
