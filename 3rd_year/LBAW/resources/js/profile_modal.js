export function setupProfileModal() {

    const profilePictureInput = document.getElementById('profile-picture-input');
    const profilePicturePreview = document.querySelector('[data-profile-picture-preview]');
    const removeProfilePictureInput = document.querySelector('[data-remove-profile-picture-input]');
    const removeProfilePictureButton = document.querySelector('[data-remove-profile-picture]');
    const defaultProfilePictureUrl = profilePicturePreview?.dataset.profilePictureDefault;

    let objectUrl = null;

    // IMAGE PREVIEW
    profilePictureInput?.addEventListener("change", () => {
        if (!profilePictureInput.files?.length) return;

        if (objectUrl) URL.revokeObjectURL(objectUrl);

        objectUrl = URL.createObjectURL(profilePictureInput.files[0]);
        profilePicturePreview.src = objectUrl;

        removeProfilePictureButton.disabled = false;
        removeProfilePictureInput.value = "0";
    });

    // REMOVE IMAGE
    removeProfilePictureButton?.addEventListener("click", () => {
        if (objectUrl) URL.revokeObjectURL(objectUrl);

        profilePictureInput.value = "";
        profilePicturePreview.src = defaultProfilePictureUrl;
        removeProfilePictureInput.value = "1";

        removeProfilePictureButton.disabled = true;
    });

    // SWITCH PROFILE ⇄ PASSWORD SECTIONS
    const profileSection = document.querySelector('[data-profile-section]');
    const passwordSection = document.querySelector('[data-password-section]');
    const openPassword = document.querySelector('[data-open-password-section]');
    const backBtn = document.querySelector('[data-back-to-profile]');

    openPassword?.addEventListener("click", () => {
        profileSection.classList.add("hidden");
        passwordSection.classList.remove("hidden");
    });

    backBtn?.addEventListener("click", () => {
        passwordSection.classList.add("hidden");
        profileSection.classList.remove("hidden");
    });

    // PASSWORD VISIBILITY
    document.querySelectorAll('[data-password-visibility-toggle]').forEach(button => {
        button.addEventListener("click", () => {
            const id = button.dataset.target;
            const input = document.getElementById(id);
            const icon = button.querySelector("i");

            const isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";

            icon.classList.toggle("bi-eye", isHidden);
            icon.classList.toggle("bi-eye-slash", !isHidden);
        });
    });

    // PASSWORD CRITERIA
    const passwordInput = document.getElementById("profile-password");
    const criteriaList = document.querySelector("[data-password-criteria]");

    passwordInput?.addEventListener("input", e => {
        const v = e.target.value;

        const status = {
            length: v.length >= 8,
            uppercase: /[A-Z]/.test(v),
            lowercase: /[a-z]/.test(v),
            number: /\d/.test(v),
            symbol: /[^A-Za-z0-9]/.test(v)
        };

        Object.entries(status).forEach(([key, passed]) => {
            const li = criteriaList.querySelector(`[data-criterion="${key}"]`);
            const dot = li.querySelector("span");

            li.classList.toggle("text-emerald-600", passed);
            li.classList.toggle("text-slate-500", !passed);

            dot.classList.toggle("bg-emerald-500", passed);
            dot.classList.toggle("bg-slate-300", !passed);
        });
    });
   const statusSelect = document.querySelector('[data-status-select]');
    const customWrapper = document.querySelector('[data-custom-status-wrapper]');
    const customInput = document.getElementById('profile-custom-status');

    if (statusSelect && customWrapper && customInput) {
        function updateCustomStatusVisibility() {
            const isCustom = statusSelect.value === 'customizável';

            customWrapper.classList.toggle('hidden', !isCustom);

            if (!isCustom) {
                customInput.value = '';
            }
        }

        statusSelect.addEventListener('change', updateCustomStatusVisibility);
        updateCustomStatusVisibility();
    }
}