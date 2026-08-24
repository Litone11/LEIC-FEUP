
export function setupTaskStatusUpdater() {
    document.querySelectorAll('[data-task-status]').forEach(select => {
        select.addEventListener('change', () => {
            const id = select.getAttribute('data-task-status');
            const form = document.querySelector(`[data-task-update-form="${id}"]`);

            if (!form) return;

            const input = form.querySelector('[name="status"]');
            if (input) {
                input.value = select.value;
            }

            form.submit();
        });
    });
}
