<section class="mt-10">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-slate-900">Projetos ativos</h2>
        <a href="{{ route('projects') }}" class="text-sm font-medium text-atlas-500 hover:text-atlas-900">
            Ver tudo
        </a>
    </div>

    <div class="mt-4 grid gap-6 lg:grid-cols-2">
        @forelse ($projects as $project)

            @php
                $isCoordinator = $project['is_coordinator'] ?? false;
            @endphp

            <a href="{{ route('projects.show', $project['id']) }}"
               class="relative rounded-3xl border border-slate-200 bg-white p-6 shadow-sm block
                      hover:border-atlas-300 hover:shadow-md transition cursor-pointer">

                {{-- Coordinator badge --}}
                @if ($isCoordinator)
                    <span class="absolute right-4 top-4 inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 
                                 text-[10px] font-semibold text-amber-700 border border-amber-300">
                        <i class="bi bi-award-fill text-amber-600"></i> Cord.
                    </span>
                @endif

                <p class="text-lg font-semibold text-slate-900">
                    {{ $project['name'] }}
                </p>

                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Progresso</p>

                    <div class="mt-2 h-2 rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-atlas-500" style="width: {{ $project['progress'] }}%"></div>
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $project['progress'] }}% completo
                    </p>
                </div>

                <div class="mt-6 flex items-center gap-4 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1">
                        {{ $project['tasks_total'] }} tarefas
                    </span>

                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1">
                        {{ $project['tasks_done'] }} concluídas
                    </span>
                </div>
            </a>

        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
                Ainda não tens projetos associados. Adiciona um a partir do módulo de projetos.
            </div>
        @endforelse
    </div>
</section>
