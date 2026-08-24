export function setupMemberManagement() {
    const manageBtn = document.getElementById("manageMembersBtn");
    const addBtn = document.getElementById("addMemberBtn");
    const inviteBtn = document.getElementById("inviteByEmailBtn");
    const rows = document.querySelectorAll(".member-row");

    const userMeta = document.querySelector('meta[name="user-id"]');
    const removeForm = document.querySelector('#removeMemberForm');

    if (
        !manageBtn ||
        !addBtn ||
        !inviteBtn ||
        !userMeta ||
        !removeForm ||
        rows.length === 0
    ) {
        return;
    }

    const currentUserId = userMeta.content;
    const projectId = removeForm.dataset.projectId;

    let managementMode = false;

    manageBtn.addEventListener("click", () => {
        managementMode = !managementMode;

        manageBtn.innerHTML = managementMode
            ? '<i class="bi bi-x-lg text-amber-600"></i> Sair de gestão'
            : '<i class="bi bi-gear-fill text-amber-600"></i> Gerir membros';

        addBtn.classList.toggle("hidden", !managementMode);
        inviteBtn.classList.toggle("hidden", !managementMode);

        rows.forEach(row => {
            const badge = row.querySelector(".role-badge");
            const removeBtn = row.querySelector(".removeMemberBtn");
            const promoteBtn = row.querySelector(".promoteMemberBtn");

            if (!badge || !removeBtn || !promoteBtn) return;

            const memberId = row.dataset.memberId;

            if (memberId === currentUserId) {
                removeBtn.classList.add("hidden");
                promoteBtn.classList.add("hidden");
                return;
            }

            badge.classList.toggle("hidden", managementMode);
            removeBtn.classList.toggle("hidden", !managementMode);
            promoteBtn.classList.toggle("hidden", !managementMode);

            promoteBtn.addEventListener("click", () => {
                const modal = document.getElementById("make-coordinator-modal");
                if (!modal) return;

                const nameSpan = modal.querySelector("#promoteMemberName");
                const form = modal.querySelector("#promoteMemberForm");

                if (!nameSpan || !form) return;

                nameSpan.textContent = promoteBtn.dataset.memberName;
                form.action = `/projects/${projectId}/members/${promoteBtn.dataset.memberId}/promote`;
            });
        });
    });
}

export function setupRemoveMemberPopup() {
    const nameSpan = document.getElementById("removeMemberName");
    const form = document.getElementById("removeMemberForm");

    document.querySelectorAll(".removeMemberBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            const userId = btn.dataset.memberId;
            const username = btn.dataset.memberName;

            nameSpan.textContent = username;

            form.action = `/projects/${form.dataset.projectId}/members/${userId}`;
        });
    });
}

export async function openUserProfile(userId) {
    const res = await fetch(`/users/${userId}/profile`);
    if (!res.ok) return;

    const data = await res.json();

    document.getElementById('up-username').textContent = data.username;
    document.getElementById('up-email').textContent = data.email;
    document.getElementById('up-status').textContent = data.status;
    document.getElementById('up-joined').textContent = data.joined_at;

    const avatar = document.getElementById('up-avatar');
    avatar.src = data.profile_pic ?? '/images/default-profile.svg';

   const statusRow = document.getElementById('up-status-row');
    const statusSpan = document.getElementById('up-status');
    const custom = document.getElementById('up-custom-status');

    if (data.status === 'customizável' && data.custom_status) {
        statusSpan.textContent = `“${data.custom_status}”`;

        custom.classList.add('hidden');
        custom.textContent = '';
    } else {
        statusSpan.textContent = data.status;

        custom.classList.add('hidden');
        custom.textContent = '';
    }

    const trigger = document.getElementById('open-user-profile-trigger');
    if (trigger) trigger.click();
}

export function setupMemberProfileView() {
    document.querySelectorAll('.member-row').forEach(row => {
        row.addEventListener('click', () => {
            openUserProfile(row.dataset.memberId);
        });
    });

    document.querySelectorAll('.removeMemberBtn, .promoteMemberBtn').forEach(btn => {
        btn.addEventListener('click', (e) => e.stopPropagation());
    });
}
