const getCsrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.content || '';

const parseJson = (value, fallback = { predecessors: [], successors: [] }) => {
    try {
        return value ? JSON.parse(value) : fallback;
    } catch {
        return fallback;
    }
};

const dependencyTemplate = (item, type, canManage, deleteTemplate, csrfToken) => {
    const action = deleteTemplate ? deleteTemplate.replace('__ID__', item.id) : '';
    const removeButton = canManage && action
        ? `<form method="POST"
                   action="${action}"
                   data-dependency-delete-form
                   data-dependency-role="${type}"
                   data-dependency-id="${item.id}">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit"
                        class="text-sm text-rose-500 hover:text-rose-600"
                        data-remove-dependency="${item.id}"
                        data-dependency-role="${type}">
                    <i class="bi bi-trash"></i>
                </button>
           </form>`
        : '';

    return `
        <li class="flex items-center justify-between rounded-2xl border border-slate-100 px-3 py-2 bg-slate-50"
            data-dependency-id="${item.id}"
            data-dependency-role="${type}">
            <div>
                <a href="/tasks/${item.task_id}"
                   class="font-medium text-atlas-700 hover:text-atlas-900">
                    ${item.name}
                </a>
                <p class="text-xs text-slate-500">Estado: ${item.status ?? '—'}</p>
            </div>
            ${removeButton}
        </li>
    `;
};

export function setupTaskDependencies() {
    const containers = document.querySelectorAll('[data-task-dependencies]');
    if (!containers.length) return;

    const csrfToken = getCsrfToken();

    containers.forEach(container => {
        const canManage = container.dataset.canManage === '1';

        const initial = parseJson(container.dataset.initialDependencies, {
            predecessors: [],
            successors: [],
        });

        let state = {
            predecessor: initial.predecessors || [],
            successor: initial.successors || [],
        };

        const showToast = (message, type = 'success') => {
            if (typeof window.showFlashToast === 'function') {
                window.showFlashToast({ message, type });
            }
        };

        const renderList = type => {
            const list = container.querySelector(`[data-dependency-list="${type}"]`);
            if (!list) return;

            const items = state[type] || [];
            if (!items.length) {
                const emptyLabel = type === 'predecessor'
                    ? 'Nenhum predecessor definido.'
                    : 'Nenhum sucessor definido.';
                list.innerHTML = `<li class="text-sm text-slate-500">${emptyLabel}</li>`;
                return;
            }

            list.innerHTML = items
                .map(item => dependencyTemplate(item, type, canManage, container.dataset.deleteUrlTemplate, csrfToken))
                .join('');
       };

        const render = () => {
            renderList('predecessor');
            renderList('successor');
        };

        const submitDependency = (form) => {
            const type = form.dataset.type;
            const action = form.getAttribute('action') || container.dataset.storeUrl;

            if (!action || typeof fetch !== 'function') {
                form.submit();
                return;
            }

            const formData = new FormData(form);
            if (!formData.has('type')) {
                formData.append('type', type);
            }
            container.classList.add('opacity-50');

            fetch(action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(body => {
                            throw new Error(body.message || 'Não foi possível adicionar a dependência.');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.dependency && data.type) {
                        const bucket = data.type === 'predecessor' ? 'predecessor' : 'successor';
                        state[bucket] = [...(state[bucket] || []), data.dependency];
                        render();
                        form.reset();
                        showToast('Dependência adicionada.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast(err.message || 'Erro ao criar dependência.', 'error');
                })
                .finally(() => {
                    container.classList.remove('opacity-50');
                });
        };

        const deleteDependency = (url, role, id, fallbackForm) => {
            if (!url || typeof fetch !== 'function') {
                fallbackForm?.submit();
                return;
            }

            container.classList.add('opacity-50');

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            })
                .then(res => {
                    if (!res.ok) throw new Error('Não foi possível remover a dependência.');
                })
                .then(() => {
                    const bucket = role === 'successor' ? 'successor' : 'predecessor';
                    state[bucket] = (state[bucket] || []).filter(item => String(item.id) !== String(id));
                    render();
                    showToast('Dependência removida.');
                })
                .catch(err => {
                    console.error(err);
                    showToast(err.message || 'Erro ao remover dependência.', 'error');
                })
                .finally(() => {
                    container.classList.remove('opacity-50');
                });
        };

        render();

        container.addEventListener('submit', event => {
            const createForm = event.target.closest('[data-dependency-form]');
            if (createForm) {
                if (!canManage) return;
                event.preventDefault();
                submitDependency(createForm);
                return;
            }

            const deleteForm = event.target.closest('[data-dependency-delete-form]');
            if (deleteForm) {
                const action = deleteForm.getAttribute('action');
                const id = deleteForm.dataset.dependencyId;
                const role = deleteForm.dataset.dependencyRole || 'predecessor';
                if (!action || !id) return;

                if (typeof fetch !== 'function') return;

                event.preventDefault();
                deleteDependency(action, role, id, deleteForm);
            }
        });
    });
}
