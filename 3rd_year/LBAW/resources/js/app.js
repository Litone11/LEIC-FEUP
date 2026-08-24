import './bootstrap';
import axios from 'axios';
import './echo'; 
import { setupRealtimeNotifications } from './notifications.js';
import { setupFlashToast } from './flash_toast';
import { setupModal } from './modal.js';
import { setupProjectTags, setupTaskTagSelection } from './tag.js';
import { setupProjectSearch, setupFavoriteToggle, setupArchiveToggle, addMembersProject } from './projects.js';
import { setupUsersPage } from './admin_users.js';
import { setupTaskStatusUpdater } from './tasks.js';
import { setupTaskComments } from './task_comments.js';
import { setupTaskDependencies } from './task_dependencies.js';
import { setupDashboardSearch } from './dashboard_search.js';
import { setupProfileModal } from './profile_modal.js';
import { setupMemberManagement, setupRemoveMemberPopup, setupMemberProfileView  } from './project_members.js';
import { setupCalendar } from './calendar';
import { setupContextualHelp } from './contextual_help.js';
import { setupAdminActions } from './admin_actions.js';
import { initSidebarMobile } from './sidebar_mobile.js';
import { mountToasts } from './toasts.js';



console.log('VITE LOADED');

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

//this function fixes the ordering problem of when inicializer crashes and stops the rest from working
function safeInit(name, fn) {
    try {
        fn();
        console.log(`${name} initialized`);
    } catch (err) {
        console.error(`${name} failed`, err);
    }
}

function setupMemberSearch() {
    const input = document.getElementById('membersSearch');
    if (!input) return;

    const rows = document.querySelectorAll('.member-row');
    const normalize = (text) => (text || '').toLowerCase();

    input.addEventListener('input', () => {
        const term = normalize(input.value);

        rows.forEach(row => {
            const name = normalize(row.dataset.memberName);
            const email = normalize(row.dataset.memberEmail);
            const match = !term || name.includes(term) || email.includes(term);
            row.classList.toggle('hidden', !match);
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing DOM...');

    // Models
    safeInit('Create modal', () =>
        setupModal({
            modalId: 'create-modal',
            openSelector: '[data-open-modal="create-modal"]',
            closeSelector: '[data-close-modal]'
        })
    );

    safeInit('Admin modal', () =>
        setupModal({
            modalId: 'admin_user_modal',
            openSelector: '[data-open-modal="admin-user-modal"]',
            closeSelector: '[data-close-modal]',
            focusSelector: '[data-focus]',
            lockScroll: true
        })
    );

    safeInit('Admin suspend modal', () =>
        setupModal({
            modalId: 'admin_suspend_modal',
            openSelector: '.open-suspend-modal',
            closeSelector: '[data-close-modal]',
            focusSelector: '#suspendReasonInput',
            lockScroll: true
        })
    );

    safeInit('Task modal', () =>
        setupModal({
            modalId: 'task-modal',
            openSelector: '[data-open-modal="task-modal"]',
            closeSelector: '[data-close-modal]',
            focusSelector: '[data-focus]',
            lockScroll: true
        })
    );

    safeInit('Task edit modal', () =>   
        setupModal({
            modalId: 'task-edit-modal',
            openSelector: '[data-open-modal="task-edit-modal"]',
            closeSelector: '[data-close-modal]',
            focusSelector: '[name="name"]',
            lockScroll: true
        })
    );


    safeInit('Profile modal', () =>
        setupModal({
            modalId: 'profile-modal',
            openSelector: '[data-open-modal="profile-modal"]',
            closeSelector: '[data-close-modal]',
            focusSelector: '#profile-username',
        })
    );

    safeInit('Forum topic modal', () =>
        setupModal({
            modalId: 'forum-topic-modal',
            openSelector: '[data-open-modal="forum-topic-modal"]',
            closeSelector: '[data-close-modal]',
            focusSelector: 'input[name="title"]'
        })
    );

    safeInit('Add member modal', () =>
        setupModal({
            modalId: 'add-member-modal',
            openSelector: '[data-open-modal="add-member-modal"]',
            closeSelector: '[data-close-modal]',
            focusSelector: '#addMemberEmailInput'
        })
    );

    safeInit('Invite member modal', () =>
        setupModal({
            modalId: 'invite-member-modal',
            openSelector: '#inviteByEmailBtn',
            closeSelector: '[data-close-modal]',
            focusSelector: 'input[name="email"]'
        })
    );

    safeInit('Remove member modal', () =>
        setupModal({
            modalId: 'remove-member-modal',
            openSelector: '[data-open-modal="remove-member-modal"]',
            closeSelector: '[data-close-modal]'
        })
    );

    safeInit('Make coordinator modal', () =>
        setupModal({
            modalId: 'make-coordinator-modal',
            openSelector: '[data-open-modal="make-coordinator-modal"]',
            closeSelector: '[data-close-modal]'
        })
    );

    safeInit('Delete task modal', () =>
        setupModal({
            modalId: 'delete-task-modal',
            openSelector: '[data-open-modal="delete-task-modal"]',
            closeSelector: '[data-close-modal]'
        })
    );

    safeInit('Profile delete modal', () =>
        setupModal({
            modalId: 'profile_delete_confirmation',
            openSelector: '[data-open-modal="profile_delete_confirmation_modal"]',
            closeSelector: '[data-close-modal]',
            lockScroll: true
        })
    );
    
    safeInit('Admin New Account Modal', () =>
        setupModal({
            modalId: 'admin_new_account_modal',
            openSelector: '[data-open-modal="addNewAccount"]',
            closeSelector: '[data-close-modal]'
        })
    );

    safeInit('Profile Member Modal', () =>
        setupModal({
            modalId: 'user-profile-modal',
            openSelector: '[data-open-user-profile]',
            closeSelector: '[data-close-modal]',
            lockScroll: true
        })
    );

    // adding members logic
    safeInit('Add members (create modal)', () =>
        addMembersProject({
            modalId: "create-modal",
            inputId: "memberEmailInput",
            listId: "memberList",
            hiddenId: "members",
            addBtnId: "addMemberBtn"
        })
    );

    // Add members after project is created
    safeInit('Add members (add-member modal)', () =>
        addMembersProject({
            modalId: "add-member-modal",
            inputId: "addMemberEmailInput",
            listId: "addMemberList",
            hiddenId: "addMembersHidden",
            addBtnId: "addMemberToListBtn"
        })
    );

    // delete task modal
    safeInit('Delete task modal', () =>
        setupModal({
            modalId: 'delete-task-modal',
            openSelector: '[data-open-modal="delete-task-modal"]',
            closeSelector: '[data-close-modal]'
        })
    );

    safeInit('Delete User', () =>
    setupModal({
        modalId:'profile_delete_confirmation',
        openSelector: '[data-open-modal="profile_delete_confirmation_modal"]',
        closeSelector: "[data-close-modal]",
        lockScroll:true
    })
    );
    safeInit('Admin New Account Modal', () =>
        setupModal({
        modalId: 'admin_new_account_modal',
        openSelector: '[data-open-modal="addNewAccount"]',
        closeSelector: '[data-close-modal]'
    })
    );

    safeInit('Profile Member Modal', () =>
    setupModal({
        modalId: 'user-profile-modal',
        openSelector: '[data-open-user-profile]',
        closeSelector: '[data-close-modal]',
        lockScroll: true
    })
    );

    safeInit('Project Tags', setupProjectTags);
    safeInit('Profile modal logic', setupProfileModal);
    safeInit('Dashboard search', setupDashboardSearch);
    safeInit('Users page', setupUsersPage);
    safeInit('Task status updater', setupTaskStatusUpdater);
    safeInit('Archive toggle', setupArchiveToggle);
    safeInit('Task comments', setupTaskComments);
    safeInit('Task dependencies', setupTaskDependencies);
    safeInit('Project search', setupProjectSearch);
    safeInit('Favorite toggle', setupFavoriteToggle);
    safeInit('Member management', setupMemberManagement);
    safeInit('Remove member popup', setupRemoveMemberPopup);
    safeInit('Realtime notifications', setupRealtimeNotifications);
    safeInit('Flash toast', setupFlashToast);
    safeInit('Calendar', setupCalendar);
    safeInit('Member Profile View', setupMemberProfileView);
    safeInit('Contextual help', setupContextualHelp);
    safeInit('Admin actions', setupAdminActions);
    safeInit('Member search', setupMemberSearch);
    safeInit('Profile Member Modal', () =>
        initSidebarMobile({
        openBtnId: "openSidebar",
        closeBtnId: "closeSidebar",
        closeOnLinkClick: true,
    }));
    safeInit('mountToasts', mountToasts);
    safeInit('mountToasts', setupTaskTagSelection);
});
