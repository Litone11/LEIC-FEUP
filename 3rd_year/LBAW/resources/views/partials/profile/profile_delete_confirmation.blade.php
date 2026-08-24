<div id="profile_delete_confirmation" class="hidden fixed inset-0 items-center justify-center z-50 bg-black bg-opacity-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold">Tem a certeza que pretende continuar?</h2>
    </div>
    <p class="mt-4 text-sm text-gray-600">Esta ação é irreversível. Toda a sua interação será anonimizada.</p>
    
    <form method="POST" action="{{ route('profile.delete') }}">
      @csrf
      <div class="mt-6 flex justify-end gap-2">
        <!-- Cancel just closes the modal -->
        <button type="button" data-close-modal class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancelar</button>

        <button type="submit" class="px-4 py-2 rounded-lg bg-atlas-500 text-white text-sm">Confirmar</button>
      </div>
    </form>
  </div>
</div>
