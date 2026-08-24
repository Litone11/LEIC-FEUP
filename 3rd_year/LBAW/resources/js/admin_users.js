export function setupUsersPage() {
    const buildRoute = (template, userId) => template.replace('__USER__', userId);

    const searchInput = document.getElementById('userSearch');
    let tableRows = Array.from(document.querySelectorAll('#usersTable tbody tr.user-row'));

    const modalUsername = document.getElementById('modalUsername');
    const modalEmail = document.getElementById('modalEmail');
    const modalSave = document.getElementById('modalSave');
    const modalCancel = document.getElementById('modalCancel');
    const modalIsAdmin = document.getElementById('modalIsAdmin');
    const modalBlockStatus = document.getElementById('modalBlockStatus');
    const modalBlockReason = document.getElementById('modalBlockReason');
    const modalBlockBtn = document.getElementById('modalBlock'); // bloquear
    const modalUnblockBtn = document.getElementById('modalUnblock'); // desbloquear
    const modalDeleteBtn = document.getElementById('modalDelete'); // eliminar

    if (
        !searchInput ||
        !modalUsername ||
        !modalEmail ||
        !modalSave ||
        !modalCancel ||
        !modalIsAdmin ||
        tableRows.length === 0
    ) {
        return;
    }


    let selectedUserId = null;
    let selectedRow = null;

    // ===============================
    // SEARCH FILTER (AJAX)
    // ===============================
    const renderUsers = (rows) => {
        const tbody = document.querySelector('#usersTable tbody');
        if (!tbody) return;

        tbody.innerHTML = rows.map(user => `
            <tr class="user-row cursor-pointer"
                data-id="${user.id}"
                data-username="${user.username}"
                data-email="${user.email}"
                data-is-admin="${user.is_admin ? '1' : '0'}"
                data-blocked="${user.blocked ? '1' : '0'}"
                data-block-reason="${user.block_reason || ''}">
                <td class="px-6 py-4">${user.username}</td>
                <td class="px-6 py-4">${user.email}</td>
                <td class="px-6 py-4">
                    <span class="${user.is_admin ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'} inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold">
                        ${user.is_admin ? 'Sim' : 'Não'}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="${user.blocked ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'} inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold">
                        ${user.blocked ? 'Blocked' : 'Active'}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <button type="button"
                            class="edit-user-btn rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-atlas-200 hover:bg-atlas-50 hover:text-atlas-600"
                            data-open-modal="admin-user-modal">
                        Editar
                    </button>
                </td>
            </tr>
        `).join('');

        // rebind rows
        tableRows = Array.from(document.querySelectorAll('#usersTable tbody tr.user-row'));
        bindRowClicks();
    };

    const fetchUsers = (query) => {
        const url = `/admin/users/search?query=${encodeURIComponent(query)}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(res => res.json())
            .then(data => {
                if (!Array.isArray(data.users)) return;
                renderUsers(data.users);
            })
            .catch(err => console.error('Admin user search failed', err));
    };

    let debounce;
    searchInput.addEventListener('input', function () {
        const term = this.value.trim();
        clearTimeout(debounce);
        debounce = setTimeout(() => fetchUsers(term), 250);
    });

    const updateBlockUI = (isBlocked, reason) => {
        if (modalBlockStatus) {
            modalBlockStatus.textContent = isBlocked ? 'Blocked' : 'Active';
            modalBlockStatus.className = isBlocked
                ? 'inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold bg-rose-50 text-rose-600'
                : 'inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold bg-emerald-50 text-emerald-600';
        }
        if (modalBlockReason) {
            modalBlockReason.value = reason || '';
        }
    };

    const bindRowClicks = () => {
        tableRows.forEach(row => {
            row.addEventListener('click', () => {
                selectedUserId = row.dataset.id;
                selectedRow = row;

                modalUsername.value = row.dataset.username;
                modalEmail.value = row.dataset.email;
                modalIsAdmin.checked = row.dataset.isAdmin === '1';

                if (modalBlockStatus) {
                    modalBlockStatus.textContent = row.dataset.blocked === '1' ? 'Blocked' : 'Active';
                }
                if (modalBlockReason) {
                    modalBlockReason.value = row.dataset.blockReason || '';
                }
                if (typeof updateBlockUI === 'function') {
                    updateBlockUI(row.dataset.blocked === '1', row.dataset.blockReason || '');
                }

                if (window.open_admin_user_modal) {
                    window.open_admin_user_modal();
                }
            });
        });
    };

    bindRowClicks();

    // ===============================
    // CANCEL (just close modal)
    // ===============================
    modalCancel.addEventListener('click', () => {
        if (window.close_admin_user_modal) {
            window.close_admin_user_modal();
        }
    });

    const showToast = (message, type = 'success') => {
        if (window.showFlashToast) {
            window.showFlashToast({ message, type });
        } else {
            console[type === 'error' ? 'error' : 'log'](message);
        }
    };

    // ===============================
    // SAVE USER
    // ===============================
    modalSave.addEventListener('click', () => {
        if (!selectedUserId) return;

        const data = {
            id: selectedUserId,
            username: modalUsername.value,
            email: modalEmail.value,
            is_admin: modalIsAdmin.checked ? 1 : 0,
        };

        fetch(window.updateUserRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    showToast(res.message || 'Não foi possível atualizar o utilizador.', 'error');
                    return;
                }

                const row = tableRows.find(r => r.dataset.id === selectedUserId);
                if (!row) return;

                row.dataset.username = data.username;
                row.dataset.email = data.email;
                row.dataset.isAdmin = res.is_admin ? '1' : '0';

                row.cells[0].textContent = data.username;
                row.cells[1].textContent = data.email;

                const badge = row.cells[2].querySelector('span');
                if (badge) {
                    badge.textContent = res.is_admin ? 'Sim' : 'Não';
                    badge.className = res.is_admin
                        ? 'bg-emerald-50 text-emerald-600 inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold'
                        : 'bg-amber-50 text-amber-600 inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold';
                }

                if (window.close_admin_user_modal) {
                    window.close_admin_user_modal();
                }
                showToast('Utilizador atualizado com sucesso.');
            })
            .catch(error => {
                console.error(error);
                showToast('Ocorreu um erro ao atualizar o utilizador.', 'error');
            });
    });

    if (modalBlockBtn && modalUnblockBtn && modalDeleteBtn && modalBlockReason && modalBlockStatus) {
        // ===============================
        // BLOCK USER
        // ===============================
        modalBlockBtn.addEventListener('click', () => {
            if (!selectedUserId) return;
            const reason = modalBlockReason.value.trim();
            if (!reason) {
                alert('Indica a razão do bloqueio.');
                return;
            }
            if (!window.blockUserRouteTemplate) {
                alert('Rota de bloqueio não definida.');
                return;
            }

            fetch(buildRoute(window.blockUserRouteTemplate, selectedUserId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({ reason })
            })
                .then(res => res.json())
                .then(res => {
                    if (!res.success) {
                        showToast(res.message || 'Não foi possível bloquear o utilizador.', 'error');
                        return;
                    }
                    if (typeof updateBlockUI === 'function') {
                        updateBlockUI(true, res.block_reason);
                    }

                    if (selectedRow) {
                        selectedRow.dataset.blocked = '1';
                        selectedRow.dataset.blockReason = res.block_reason || '';
                        const badge = selectedRow.cells[3]?.querySelector('span');
                        if (badge) {
                            badge.textContent = 'Blocked';
                            badge.className = 'bg-rose-50 text-rose-600 inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold';
                        }
                    }
                    showToast('Utilizador bloqueado com sucesso.');
                })
                .catch(error => {
                    console.error(error);
                    showToast('Erro inesperado ao bloquear o utilizador.', 'error');
                });
        });

        // ===============================
        // UNBLOCK USER
        // ===============================
        modalUnblockBtn.addEventListener('click', () => {
            if (!selectedUserId) return;
            if (!window.unblockUserRouteTemplate) {
                alert('Rota de desbloqueio não definida.');
                return;
            }

            fetch(buildRoute(window.unblockUserRouteTemplate, selectedUserId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                }
            })
                .then(res => res.json())
                .then(res => {
                    if (!res.success) {
                        showToast(res.message || 'Não foi possível desbloquear o utilizador.', 'error');
                        return;
                    }
                    if (typeof updateBlockUI === 'function') {
                        updateBlockUI(false, null);
                    }

                    if (selectedRow) {
                        selectedRow.dataset.blocked = '0';
                        selectedRow.dataset.blockReason = '';
                        const badge = selectedRow.cells[3]?.querySelector('span');
                        if (badge) {
                            badge.textContent = 'Active';
                            badge.className = 'bg-emerald-50 text-emerald-600 inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold';
                        }
                    }
                    showToast('Utilizador desbloqueado com sucesso.');
                })
                .catch(error => {
                    console.error(error);
                    showToast('Erro inesperado ao desbloquear o utilizador.', 'error');
                });
        });

        // ===============================
        // DELETE USER
        // ===============================
        modalDeleteBtn.addEventListener('click', () => {
            if (!selectedUserId) return;
            if (!window.deleteUserRouteTemplate) {
                alert('Rota de eliminação não definida.');
                return;
            }
            const proceed = confirm('Tens a certeza que queres eliminar este utilizador?');
            if (!proceed) return;

            fetch(buildRoute(window.deleteUserRouteTemplate, selectedUserId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
                .then(res => res.json())
                .then(res => {
                    if (!res.success) {
                        showToast(res.message || 'Não foi possível eliminar o utilizador.', 'error');
                        return;
                    }
                    if (selectedRow) {
                        selectedRow.remove();
                    }
                    if (window.close_admin_user_modal) {
                        window.close_admin_user_modal();
                    }
                    showToast('Conta eliminada com sucesso.');
                })
                .catch(error => {
                    console.error(error);
                    showToast('Erro inesperado ao eliminar o utilizador.', 'error');
                });
        });
    }
}