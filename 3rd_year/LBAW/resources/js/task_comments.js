const getCsrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.content || '';

const parseJson = (value, fallback = []) => {
    try {
        return value ? JSON.parse(value) : fallback;
    } catch {
        return fallback;
    }
};

const commentTemplate = (comment, deleteTemplate, csrfToken) => {
    const action = deleteTemplate ? deleteTemplate.replace('__ID__', comment.id) : '';
    const removeButton = comment.can_delete && action
        ? `<form method="POST"
                   action="${action}"
                   class="mt-3 inline-flex"
                   data-comment-delete-form
                   data-comment-id="${comment.id}">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit"
                        class="text-xs text-rose-500 hover:text-rose-600"
                        data-delete-comment="${comment.id}">
                    Remover
                </button>
           </form>`
        : '';

    return `
        <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3" data-comment-id="${comment.id}">
            <div class="flex items-center justify-between text-sm">
                <p class="font-medium text-slate-800">${comment.author}</p>
                <span class="text-xs text-slate-500">${comment.created_at ?? ''}</span>
            </div>
            <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">${comment.message}</p>
            ${removeButton}
        </div>
    `;
};

export function setupTaskComments() {
    const containers = document.querySelectorAll('[data-task-comments]');
    if (!containers.length) return;

    const csrfToken = getCsrfToken();

    containers.forEach(container => {
        const list = container.querySelector('[data-comments-list]');
        const form = container.querySelector('[data-comment-form]');

        let comments = parseJson(container.dataset.initialComments, []);

        const render = () => {
            if (!list) return;

            if (!comments.length) {
                list.innerHTML = '<p class="text-sm text-slate-500" data-comments-empty>Sem comentários nesta tarefa.</p>';
                return;
            }

            list.innerHTML = comments
                .map(comment => commentTemplate(comment, container.dataset.deleteUrlTemplate, csrfToken))
                .join('');
        };

        const showToast = (message, type = 'success') => {
            if (typeof window.showFlashToast === 'function') {
                window.showFlashToast({ message, type });
            }
        };

        const submitComment = url => {
            if (!url || !form) {
                form?.submit();
                return;
            }

            const formData = new FormData(form);
            container.classList.add('opacity-50');

            fetch(url, {
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
                            throw new Error(body.message || 'Não foi possível enviar o comentário.');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.comment) {
                        comments.push(data.comment);
                        render();
                        form.reset();
                        showToast('Comentário enviado com sucesso.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast(err.message || 'Falha ao enviar o comentário.', 'error');
                })
                .finally(() => {
                    container.classList.remove('opacity-50');
                });
        };

        const deleteComment = (url, id, fallbackForm) => {
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
                    if (!res.ok) throw new Error('Não foi possível remover o comentário.');
                })
                .then(() => {
                    comments = comments.filter(comment => String(comment.id) !== String(id));
                    render();
                    showToast('Comentário removido.');
                })
                .catch(err => {
                    console.error(err);
                    showToast(err.message || 'Erro ao remover comentário.', 'error');
                })
                .finally(() => {
                    container.classList.remove('opacity-50');
                });
        };

        render();

        form?.addEventListener('submit', event => {
            const action = form.getAttribute('action') || container.dataset.storeUrl;

            if (typeof fetch !== 'function' || !action) {
                return;
            }

            event.preventDefault();
            submitComment(action);
        });

        container.addEventListener('submit', event => {
            const deleteForm = event.target.closest('[data-comment-delete-form]');
            if (!deleteForm) return;

            const action = deleteForm.getAttribute('action');
            const id = deleteForm.dataset.commentId;
            if (!action || !id) return;

            if (typeof fetch !== 'function') return;

            event.preventDefault();
            deleteComment(action, id, deleteForm);
        });

    });
}
