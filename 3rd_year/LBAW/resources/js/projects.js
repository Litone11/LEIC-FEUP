export function setupProjectSearch() {
    const input = document.querySelector('[data-project-search]');
    const sortSelect = document.querySelector('select[name="sort"]');
    const gridContainer = document.querySelector('#projectsGrid');
    const grid = document.querySelector('#projectsGrid .grid');
    const empty = document.querySelector('#emptyState');
    const archivedToggle = document.querySelector('[data-toggle-archived]');

    if (!grid || !sortSelect || !gridContainer || !empty) return;

    const originalHTML = grid.innerHTML;
    let debounce;

    function runFilter() {
        clearTimeout(debounce);

        debounce = setTimeout(() => {
            const query = input?.value.trim() ?? '';
            const sort  = sortSelect.value;

            const params = new URLSearchParams();
            if (query) params.set('search', query);
            if (sort) params.set('sort', sort);
            if (archivedToggle?.checked || sort === 'archived') params.set('archived', '1');

            const url = query
                ? `/search/projects?${params.toString()}`
                : `/projects?${params.toString()}`;

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.text())
                .then(html => {
                    if (!html.trim()) {
                        grid.innerHTML = '';
                        gridContainer.classList.add('hidden');
                        empty.classList.remove('hidden');
                    } else {
                        grid.innerHTML = html;
                        gridContainer.classList.remove('hidden');
                        empty.classList.add('hidden');
                    }
                })
                .catch(err => console.error('Project filter error:', err));
        }, 300);
    }

    input?.addEventListener('input', runFilter);

    sortSelect.addEventListener('change', runFilter);
    archivedToggle?.addEventListener('change', runFilter);
}


/**
 * Setup Favorite Toggle
 * Handles favoriting/unfavoriting projects with event delegation
 */
export function setupFavoriteToggle() {
    console.log('Favorite toggle initialized');

    document.addEventListener("click", async (e) => {
        const btn = e.target.closest(".favorite-btn");
        if (!btn) return;

        const projectId = btn.dataset.projectId;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }

        try {
            const res = await fetch(`/projects/${projectId}/favorite`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json"
                }
            });

            const data = await res.json();

            if (data.success) {
                if (data.is_favorite) {
                    btn.classList.remove('border-slate-200', 'text-slate-700', 'hover:bg-slate-50');
                    btn.classList.add('border-pink-200', 'text-pink-600', 'hover:bg-pink-50');
                    btn.innerHTML = `<i class="bi bi-heart-fill text-pink-500 text-lg"></i><span>Favorito</span>`;
                } else {
                    btn.classList.remove('border-pink-200', 'text-pink-600', 'hover:bg-pink-50');
                    btn.classList.add('border-slate-200', 'text-slate-700', 'hover:bg-slate-50');
                    btn.innerHTML = `<i class="bi bi-heart text-slate-400 text-lg"></i><span>Favoritar</span>`;
                }
            }
        } catch (err) {
            console.error('Favorite toggle error:', err);
        }
    });
}

export function setupArchiveToggle() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) return;

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.archive-btn');
        if (!btn) return;

        const isArchived = btn.dataset.archived === '1';
        const url = isArchived ? btn.dataset.unarchiveUrl : btn.dataset.archiveUrl;
        if (!url) return;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (!data.success) return;

            const nowArchived = !!data.is_archived;
            btn.dataset.archived = nowArchived ? '1' : '0';

            if (nowArchived) {
                btn.classList.remove('border-slate-200', 'text-slate-700', 'hover:bg-slate-50');
                btn.classList.add('border-amber-200', 'text-amber-700', 'hover:bg-amber-50');
                btn.innerHTML = `<i class="bi bi-arrow-counterclockwise"></i><span>Desarquivar</span>`;
            } else {
                btn.classList.remove('border-amber-200', 'text-amber-700', 'hover:bg-amber-50');
                btn.classList.add('border-slate-200', 'text-slate-700', 'hover:bg-slate-50');
                btn.innerHTML = `<i class="bi bi-archive"></i><span>Arquivar</span>`;
            }
        } catch (err) {
            console.error('Archive toggle error:', err);
        }
    });
}

export function addMembersProject({
    modalId,
    inputId,
    listId,
    hiddenId,
    addBtnId
}) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    const memberInput = modal.querySelector(`#${inputId}`);
    const memberList = modal.querySelector(`#${listId}`);
    const hiddenMembers = modal.querySelector(`#${hiddenId}`);
    const addBtn = modal.querySelector(`#${addBtnId}`);

    if (!memberInput || !memberList || !hiddenMembers || !addBtn) {
        console.error("Missing selectors in addMembersProject()", {modalId});
        return;
    }

    let members = [];

    function updateHiddenInput() {
        hiddenMembers.value = JSON.stringify(members);
    }

    function renderMembers() {
        memberList.innerHTML = "";
        members.forEach((email, index) => {
            const li = document.createElement("li");
            li.className = "flex justify-between items-center rounded-xl border px-4 py-2";
            li.innerHTML = `
                <span>${email}</span>
                <button type="button" class="text-rose-500 hover:text-rose-700">Remover</button>
            `;
            li.querySelector("button").addEventListener("click", () => {
                members.splice(index, 1);
                renderMembers();
                updateHiddenInput();
            });
            memberList.appendChild(li);
        });
    }

    addBtn.addEventListener("click", () => {
        const email = memberInput.value.trim();
        if (!email || members.includes(email)) return;

        members.push(email);
        renderMembers();
        updateHiddenInput();
        memberInput.value = "";
    });

    memberInput.addEventListener("keydown", e => {
        if (e.key === "Enter") {
            e.preventDefault();
            addBtn.click();
        }
    });
}
