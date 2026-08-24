export function setupContextualHelp() {
    const containers = document.querySelectorAll('[data-contextual-help]');

    containers.forEach(container => {
        const toggle = container.querySelector('[data-help-toggle]');
        const panel = container.querySelector('[data-help-panel]');
        const closeBtn = container.querySelector('[data-help-close]');

        if (!toggle || !panel) return;

        const open = () => {
            panel.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        };

        const close = () => {
            panel.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', () => {
            if (panel.classList.contains('hidden')) {
                open();
            } else {
                close();
            }
        });

        closeBtn?.addEventListener('click', close);

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                close();
            }
        });

        document.addEventListener('click', event => {
            if (!container.contains(event.target)) {
                close();
            }
        });
    });
}
