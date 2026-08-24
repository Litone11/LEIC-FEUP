<div
    id="make-coordinator-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm"
>
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-xl space-y-4">

        <h2 class="text-lg font-semibold text-slate-900">
            Promover membro a coordenador
        </h2>

        <p class="text-sm text-slate-600">
            Tens a certeza que queres promover 
            <span id="promoteMemberName" class="font-semibold"></span>
            a coordenador deste projeto?
        </p>

        <form 
            id="promoteMemberForm"
            method="POST"
            data-project-id="{{ $project->project_id }}"
        >
            @csrf
            @method('PATCH')

            <div class="flex justify-end gap-2 pt-2">
                <button type="button"
                    data-close-modal
                    class="px-4 py-2 rounded-xl border border-slate-300 text-sm">
                    Cancelar
                </button>

                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-atlas-500 text-white text-sm">
                    Promover
                </button>
            </div>
        </form>

    </div>
</div>
