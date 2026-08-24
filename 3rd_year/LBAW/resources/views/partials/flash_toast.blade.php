@if (session('success') || session('error'))
<div id="flash-toast" class="fixed top-0 inset-x-0 z-[9999]">
  <div class="px-4 pt-4 sm:px-8 lg:px-12">

    <div
      data-flash-box
      class="pointer-events-auto flex items-center justify-between rounded-2xl border px-4 py-3 text-sm shadow-sm
                transform transition-[opacity,transform] duration-300 ease-out
                -translate-y-4 opacity-0
                {{ session('success')
                    ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                    : 'bg-rose-50 border-rose-200 text-rose-800' }}"
    >
      <div class="flex items-center gap-3">
        <i class="bi {{ session('success') ? 'bi-check-circle-fill text-emerald-600' : 'bi-x-circle-fill text-rose-600' }}"></i>

        <span id="flash-toast-message">
          {{ session('success') ?? session('error') }}
        </span>
      </div>

      <button
        type="button"
        data-flash-close
        class="text-xs opacity-70 hover:opacity-100"
      >
        Fechar
      </button>
    </div>
  </div>
</div>
@endif
