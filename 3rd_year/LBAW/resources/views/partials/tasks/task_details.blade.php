<div
    class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
    data-task-id="{{ $task['id'] }}"
>
    <h2 class="sr-only">Detalhes da tarefa</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        {{-- ================= LEFT COLUMN ================= --}}
        <div class="space-y-5">

            {{-- Description --}}
            <section aria-labelledby="task-description-heading">
                <h3 id="task-description-heading" class="text-sm font-semibold text-slate-700 mb-1">Descrição</h3>
                <p class="text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $task['description'] }}
                </p>
            </section>

            <div class="h-px bg-slate-100"></div>

            {{-- Dates --}}
            <section aria-labelledby="task-dates-heading" class="space-y-2">
                <h3 id="task-dates-heading" class="text-sm font-semibold text-slate-700">Datas</h3>
                <p class="text-sm text-slate-500 flex items-center gap-2">
                    <i class="bi bi-calendar-event text-slate-400"></i>
                    <strong>Criada em:</strong> {{ $task['created_at'] }}
                </p>

                <p class="text-sm text-slate-500 flex items-center gap-2">
                    <i class="bi bi-calendar-check text-slate-400"></i>
                    <strong>Prazo:</strong>
                    {{ $task['due_at'] ?? 'Sem prazo definido' }}
                </p>

                @if ($task['completed_at'])
                    <p class="text-sm text-slate-500 flex items-center gap-2">
                        <i class="bi bi-check2-circle text-slate-400"></i>
                        <strong>Concluída:</strong> {{ $task['completed_at'] }}
                    </p>
                @endif
            </section>

            <div class="h-px bg-slate-100"></div>

            {{-- Tags --}}
            <section aria-labelledby="task-tags-heading">
                <h3 id="task-tags-heading" class="text-sm font-semibold text-slate-700 mb-1">Tags</h3>

                <ul id="existingTags" class="space-y-2">
                    @forelse ($task['tags'] as $tag)
                        <li class="flex justify-between items-center">

                            <span  class="px-2 py-1 rounded-full text-xs font-semibold
                    bg-gray-100 text-gray-600">{{ $tag['name'] }}</span>


                        </li>
                    @empty
                        <li class="text-sm text-slate-500">
                            Nenhuma tag para esta tarefa.
                        </li>
                    @endforelse
                </ul>
            </section>

        </div>

        {{-- ================= RIGHT COLUMN ================= --}}
        <div class="space-y-5">

            {{-- Project --}}
            <section aria-labelledby="task-project-heading">
                <h3 id="task-project-heading" class="text-sm font-semibold text-slate-700 mb-1">Projeto</h3>
                <a
                    href="{{ route('projects.show', $task['project']->project_id) }}"
                    class="text-atlas-600 font-medium hover:text-atlas-800"
                >
                    {{ $task['project']->name }}
                </a>
            </section>

            <div class="h-px bg-slate-100"></div>

            {{-- People --}}
            <section aria-labelledby="task-people-heading" class="space-y-2">
                <h3 id="task-people-heading" class="text-sm font-semibold text-slate-700 mb-1">Equipa</h3>
                <p class="text-sm text-slate-500 flex items-center gap-2">
                    <i class="bi bi-person-badge text-slate-400"></i>
                    <strong>Responsável:</strong>
                    {{ $task['responsible_name'] ?? '—' }}
                </p>

                <p class="text-sm text-slate-500 flex items-center gap-2">
                    <i class="bi bi-person-check text-slate-400"></i>
                    <strong>Atribuída a:</strong>
                    {{ $task['assignee_name'] ?? '—' }}
                </p>
            </section>

            <div class="h-px bg-slate-100"></div>

            {{-- Priority --}}
            <section aria-labelledby="task-priority-heading">
                <h3 id="task-priority-heading" class="text-sm font-semibold text-slate-700 mb-1">Prioridade</h3>
                <span class="px-2 py-1 rounded-full text-xs font-semibold
                    @if ($task['priority'] === 'Urgent') bg-rose-50 text-rose-600
                    @elseif ($task['priority'] === 'High') bg-amber-50 text-amber-600
                    @elseif ($task['priority'] === 'Medium') bg-sky-50 text-sky-600
                    @else bg-emerald-50 text-emerald-600
                    @endif">
                    {{ $task['priority'] }}
                </span>
            </section>

            {{-- Status --}}
            <section aria-labelledby="task-status-heading">
                <h3 id="task-status-heading" class="text-sm font-semibold text-slate-700 mb-1">Estado</h3>
                <span class="px-2 py-1 rounded-full text-xs font-semibold
                    @if ($task['status'] === 'Done') bg-emerald-50 text-emerald-600
                    @elseif ($task['status'] === 'InProgress') bg-amber-50 text-amber-600
                    @else bg-slate-100 text-slate-500
                    @endif">
                    {{ $task['status'] }}
                </span>
            </section>

        </div>
    </div>
</div>
