<div class="space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">

    <div class="flex items-center justify-between text-sm">
        <a href="{{ route('projects') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-atlas-500">
            <span>&larr;</span> Voltar aos projetos
        </a>

        <div class="flex gap-2">
            <a
                href="{{ route('projects.forum', $project) }}"
                class="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-atlas-700 hover:bg-slate-50"
            >
                Fórum
            </a>
        @if ($userRole === 'coordinator')
                    <a
                        href="{{ route('projects.settings', $project) }}"
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                    >
                        Definições
                    </a>
                    
        @endif
            <form method="POST" action="{{ route('projects.members.remove', ['project' => $project, 'user' => auth()->user()]) }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="rounded-2xl border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                    Sair do projeto
                </button>
            </form>

        </div>
    </div>

    <div>
    <p class="text-sm text-slate-500">Projeto</p>

    <div class="flex items-center gap-3">
            <span
                class="h-3 w-3 rounded-full"
                style="background-color: {{ $project->color }}"
            ></span>

            <h1 class="text-3xl font-semibold text-slate-900">
                {{ $project->name }}
            </h1>
        </div>

        <p class="mt-2 text-sm text-slate-500">{{ $project->description }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Progresso do Projeto</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['progress'] }}%</p>
            <div class="mt-2 h-2 rounded-full bg-slate-200">
                <div class="h-2 rounded-full bg-atlas-500"
                     style="width: {{ $summary['progress'] }}%"></div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Todas as Tarefas</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['tasks_ratio'] }}</p>
            <p class="text-xs text-slate-500">Concluídas / Total</p>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Data de Início do Projeto</p>
            <p class="mt-2 text-xl font-semibold text-slate-900">{{ $summary['start_date'] ?? 'Sem data' }}</p>
        </div>

        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase text-slate-500">Equipa</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['team_count'] }}</p>
            <p class="text-xs text-slate-500">membros</p>
        </div>
    </div>

</div>
