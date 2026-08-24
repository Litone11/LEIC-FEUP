<div
    id="remove-member-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm"
>
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-xl space-y-4">

        <h2 class="text-lg font-semibold text-slate-900">Remover membro</h2>

        <p class="text-sm text-slate-600">
            Tens a certeza que queres remover
            <span id="removeMemberName" class="font-semibold"></span>
            deste projeto?
        </p>

        <form 
            id="removeMemberForm"
            method="POST"
            data-project-id="{{ $project->project_id }}"
        >
            @csrf
            @method('DELETE')

            <div class="flex justify-end gap-2 pt-2">
                <button
                    type="button"
                    data-close-modal
                    class="px-4 py-2 rounded-xl border border-slate-300 text-sm">
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm">
                    Remover
                </button>
            </div>
        </form>

    </div>
</div>
