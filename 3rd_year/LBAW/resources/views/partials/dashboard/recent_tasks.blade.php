<section class="mt-10">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-slate-900">Tarefas recentes</h2>
    </div>

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white">
        <div class="divide-y divide-slate-100">

            @forelse ($recentTasks as $task)
                <div class="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-medium text-slate-900">
                            {{ $task['title'] ?? $task['description'] }}
                        </p>
                        <p class="text-sm text-slate-500">{{ $task['project'] }}</p>
                    </div>

                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                        {{ $task['done'] ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                        {{ $task['done'] ? 'Concluída' : 'Em progresso' }}
                    </span>
                </div>

            @empty
                <p class="px-6 py-8 text-center text-sm text-slate-500">
                    Sem tarefas registadas ainda.
                </p>
            @endforelse

        </div>
    </div>
</section>
