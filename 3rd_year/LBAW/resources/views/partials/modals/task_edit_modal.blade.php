@if ($task['can_edit'])
<div id="task-edit-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm px-4 sm:px-6">
    <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Editar tarefa</p>
                <h2 class="text-xl font-semibold text-slate-900">{{ $task['name'] }}</h2>
            </div>
            <button type="button" data-close-modal class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>

        <div class="mt-4">
            @include('partials.tasks.edit_form', [
                'task'          => $task,
                'project'       => $project,
                'wrapperClasses'=> 'space-y-4'
            ])
        </div>
    </div>
</div>
@endif
