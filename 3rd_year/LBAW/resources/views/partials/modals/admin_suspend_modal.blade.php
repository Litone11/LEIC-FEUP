<div id="admin_suspend_modal"
     class="fixed inset-0 hidden z-50 items-center justify-center bg-slate-900/70 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-lg space-y-4">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Suspender projeto</h3>
            <p class="text-sm text-slate-500 mt-1">
                Explica a razão para suspender <span class="font-semibold text-slate-800" data-project-name>este projeto</span>.
            </p>
        </div>
        <form method="POST"
              class="space-y-4"
              data-admin-action="suspend-project"
              data-success-message="Projeto suspenso com sucesso."
              data-error-message="Não foi possível suspender o projeto."
              data-reload="true">
            @csrf
            <div>
                <label for="suspendReasonInput" class="block text-sm font-medium text-slate-700">
                    Razão da suspensão
                </label>
                <textarea id="suspendReasonInput"
                          name="reason"
                          required
                          rows="4"
                          class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm"
                          placeholder="Ex: Conteúdo inapropriado, spam, etc."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        data-close-modal
                        class="px-4 py-2 rounded-xl border text-slate-600 hover:bg-slate-50">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-amber-500 text-white hover:bg-amber-600">
                    Confirmar suspensão
                </button>
            </div>
        </form>
    </div>
</div>
