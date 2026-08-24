<div id="user-profile-modal"
     class="fixed inset-0 z-50 hidden bg-slate-900/70 flex items-center justify-center">

  <div class="bg-white rounded-3xl p-6 w-full max-w-md">

    <div class="flex items-center gap-4">
      <img id="up-avatar"
           class="h-16 w-16 rounded-full object-cover"
           src="/images/default-profile.svg"
           alt="Avatar do utilizador">

      <div>
        <h2 id="up-username" class="text-xl font-semibold"></h2>
        <p id="up-email" class="text-sm text-slate-500"></p>
      </div>
    </div>

    <div class="mt-4 space-y-2 text-sm">
    <p id="up-status-row">
    <strong>Status:</strong> <span id="up-status"></span>
    </p>      
    <p id="up-custom-status" class="italic text-slate-500 hidden"></p>
      <p><strong>Joined:</strong> <span id="up-joined"></span></p>
    </div>

    <div class="mt-6 flex justify-end">
      <button data-close-modal
              class="rounded-2xl bg-slate-200 px-4 py-2">
        Close
      </button>
    </div>
  </div>
</div>
