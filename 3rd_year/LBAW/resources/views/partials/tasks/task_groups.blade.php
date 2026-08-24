<section class="space-y-6">
    <div class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white px-6 py-5">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900">Organização das listas</h2>
            <p class="text-sm text-slate-500">Cria grupos e listas para dividir melhor o trabalho.</p>
        </div>

        @if ($isCoordinator)
            <form method="POST"
                  action="{{ route('projects.task-groups.store', $project) }}"
                  class="grid gap-3 md:grid-cols-4">
                @csrf
                <input type="text" name="name" placeholder="Nome do grupo"
                       class="rounded-2xl border border-slate-200 px-3 py-2 text-sm" required>
                <input type="text" name="label" placeholder="Etiqueta (opcional)"
                       class="rounded-2xl border border-slate-200 px-3 py-2 text-sm">
                <input type="text" name="description" placeholder="Descrição"
                       class="rounded-2xl border border-slate-200 px-3 py-2 text-sm md:col-span-2">
                <button type="submit"
                        class="rounded-2xl bg-atlas-500 px-4 py-2 text-sm font-semibold text-white hover:bg-atlas-600">
                    Novo grupo
                </button>
            </form>
        @endif

        @if ($taskGroups->isEmpty())
            <p class="text-sm text-slate-500">Ainda não existem grupos de listas para este projeto.</p>
        @else
            <div class="space-y-5">
                @foreach ($taskGroups as $group)
                    <div class="rounded-3xl border border-slate-100 bg-slate-50 p-5 space-y-4">
                        @php
                            $canManageGroup = $userRole === 'coordinator' || $group->created_by === $user->user_id;
                        @endphp
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-slate-900">{{ $group->name }}</h3>
                                @if ($group->description)
                                    <p class="text-sm text-slate-500">{{ $group->description }}</p>
                                @endif
                                <p class="text-xs text-slate-400">
                                    Criado por: {{ $group->creator?->username ?? '—' }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($canManageGroup)
                                    <form method="POST"
                                          action="{{ route('projects.task-groups.destroy', [$project, $group]) }}"
                                          onsubmit="return confirm('Apagar este grupo e todas as listas associadas?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-2xl bg-rose-50 px-3 py-1.5 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                                            Remover grupo
                                        </button>
                                    </form>
                                @endif
                                @if ($isCoordinator)
                                    <form method="POST"
                                          action="{{ route('projects.task-lists.store', $project) }}"
                                          class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="group_id" value="{{ $group->task_group_id }}">
                                        <input type="text" name="name" placeholder="Nova lista"
                                               class="rounded-2xl border border-slate-200 px-3 py-1.5 text-sm" required>
                                        <input type="text" name="description" placeholder="Descrição"
                                               class="rounded-2xl border border-slate-200 px-3 py-1.5 text-sm">
                                        <button type="submit"
                                                class="rounded-2xl bg-white px-3 py-1.5 text-sm font-semibold text-atlas-600 ring-1 ring-atlas-500 hover:bg-atlas-50">
                                            Adicionar lista
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            @forelse ($group->lists as $list)
                                @php
                                    $canManageList = $userRole === 'coordinator' || $list->created_by === $user->user_id;
                                @endphp
                                <div class="rounded-2xl border border-slate-100 bg-white p-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-slate-900">{{ $list->name }}</h4>
                                            @if ($list->description)
                                                <p class="text-xs text-slate-500">{{ $list->description }}</p>
                                            @endif
                                        </div>
                                        <span class="text-xs font-semibold text-slate-500">
                                            {{ $list->tasks_for_display->count() }} tarefas
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400">
                                        Criada por: {{ $list->creator?->username ?? '—' }}
                                    </p>

                                    <ul class="space-y-2 text-sm text-slate-600">
                                        @forelse ($list->tasks_for_display->take(3) as $task)
                                            <li class="flex items-center justify-between rounded-2xl bg-slate-50 px-3 py-2">
                                                <span>{{ $task['name'] }}</span>
                                                <small class="text-slate-400">{{ $task['status'] }}</small>
                                            </li>
                                        @empty
                                            <li class="rounded-2xl bg-slate-50 px-3 py-2 text-slate-400">
                                                Sem tarefas atribuídas.
                                            </li>
                                        @endforelse
                                    </ul>

                                    @if ($list->tasks_for_display->count() > 3)
                                        <p class="text-xs text-slate-400">
                                            e mais {{ $list->tasks_for_display->count() - 3 }} tarefas...
                                        </p>
                                    @endif

                                    @if ($isCoordinator && $assignableTasks->isNotEmpty())
                                        <form method="POST"
                                              action="{{ route('projects.task-lists.assign', [$project, $list]) }}"
                                              class="flex flex-col gap-2">
                                            @csrf
                                            <select name="task_id"
                                                    class="rounded-2xl border border-slate-200 px-3 py-1.5 text-sm"
                                                    required>
                                                <option value="">Adicionar tarefa...</option>
                                                @foreach ($assignableTasks as $assignable)
                                                    <option value="{{ $assignable['id'] }}">
                                                        {{ $assignable['name'] }}
                                                        @if ($assignable['list'])
                                                            ({{ $assignable['list'] }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit"
                                                    class="rounded-2xl bg-atlas-500 px-3 py-2 text-sm font-semibold text-white hover:bg-atlas-600">
                                                Atribuir à lista
                                            </button>
                                        </form>
                                    @endif
                                    @if ($canManageList)
                                        <form method="POST"
                                              action="{{ route('projects.task-lists.destroy', [$project, $list]) }}"
                                              onsubmit="return confirm('Apagar esta lista? As tarefas permanecerão no projeto.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="w-full rounded-2xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                                                Remover lista
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Ainda não existem listas neste grupo.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
