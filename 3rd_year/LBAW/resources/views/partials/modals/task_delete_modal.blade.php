@if ($task['can_edit'])
<div
    id="delete-task-modal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm"
    data-modal
>
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-xl space-y-4">

        <h2 class="text-lg font-semibold text-slate-900">Eliminar Tarefa</h2>

        <p class="text-sm text-slate-600">
            Tens a certeza que queres eliminar a tarefa
            <span class="font-semibold">{{ $task['name'] }}</span>?
            <br>Esta ação não pode ser desfeita.
        </p>

        <form
            id="deleteTaskForm"
            method="POST"
            action="{{ route('tasks.destroy', $task['id']) }}"
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
                    Eliminar
                </button>
            </div>
        </form>

    </div>
</div>
@endif
