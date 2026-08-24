<div class="mt-3 divide-y divide-slate-200 rounded-3xl border border-slate-100 bg-white">
    @forelse ($allTasks as $task)
        @include('partials.tasks.task_card', [
            'task' => $task,
            'canEdit' => $task['can_edit'],
            'project' => $project ?? null,
            'taskListOptions' => $taskListOptions ?? collect(),
        ])
    @empty
        <p class="px-6 py-8 text-center text-sm text-slate-500">
            Nenhuma tarefa encontrada.
        </p>
    @endforelse
</div>
