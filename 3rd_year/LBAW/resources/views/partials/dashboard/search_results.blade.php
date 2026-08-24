@if($projects->isEmpty() && $tasks->isEmpty())

    <p class="text-sm text-slate-500 text-center py-6">
        Nenhum resultado encontrado.
    </p>

@else

    <div class="flex flex-col gap-8">

        {{-- PROJETOS --}}
        @if($projects->isNotEmpty())
            <div>
                <h3 class="text-lg font-semibold text-slate-800 mb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-atlas-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
                    </svg>
                    Projetos
                </h3>

                <div class="grid gap-3">
                    @foreach($projects as $project)
                        <a href="{{ route('projects.show', $project->project_id) }}"
                           class="block p-4 bg-white border border-slate-200 rounded-xl hover:shadow-sm hover:bg-slate-50 transition">

                            <div class="text-base font-medium text-slate-900">
                                {{ $project->name }}
                            </div>

                            @if($project->description)
                                <p class="text-sm text-slate-500 mt-1">
                                    {{ $project->description }}
                                </p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- TAREFAS --}}
        @if($tasks->isNotEmpty())
            <h3 class="text-lg font-semibold text-slate-800 mb-3">Tarefas</h3>

            <div class="grid gap-3">
                @foreach($tasks as $task)

                    <a href="{{ route('tasks.show', $task->task_id) }}"
                    class="block p-4 bg-white border border-slate-200 rounded-xl hover:shadow-sm hover:bg-slate-50 transition">

                        {{-- Nome da Task --}}
                        <div class="text-base font-semibold text-slate-900">
                            {{ $task->name }}
                        </div>

                        {{-- Nome do Projeto --}}
                        @if($task->project)
                            <div class="text-sm text-slate-500 mt-1">
                                Projeto:
                                <span class="text-atlas-600 font-medium">
                                    {{ $task->project->name }}
                                </span>
                            </div>
                        @endif

                        {{-- Descrição --}}
                        @if($task->description)
                            <p class="text-sm text-slate-400 mt-1">
                                {{ $task->description }}
                            </p>
                        @endif

                    </a>

                @endforeach
            </div>
        @endif

    </div>

@endif
