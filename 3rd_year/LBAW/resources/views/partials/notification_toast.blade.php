<div id="notification-toast" class="fixed top-0 inset-x-0 z-[9999] hidden">
  <div class="px-4 pt-4 sm:px-8 lg:px-12">
    <div
      data-toast-box
      class="pointer-events-auto flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm
             transform transition-all duration-300 -translate-y-4 opacity-0"
    >
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500">
          <i class="bi bi-bell-fill text-lg"></i>
        </div>
        <span id="notification-toast-message" class="text-slate-700">
          New notification
        </span>
      </div>

      <div class="flex items-center gap-3">
        <a href="{{ route('notifications') }}"
           class="text-sm font-medium text-atlas-500 hover:underline">
          View notifications
        </a>

        <button id="notification-toast-close"
                class="text-slate-400 hover:text-slate-600 text-sm">
          Dismiss
        </button>
      </div>
    </div>
  </div>
</div>
