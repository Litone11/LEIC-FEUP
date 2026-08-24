<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" data-task-dependencies-wrapper>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Dependências da tarefa</h3>
            <p class="text-sm text-slate-500">Define a ordem de execução entre esta tarefa e as restantes do projeto.</p>
        </div>
    </div>

    <div
        data-task-dependencies
        data-store-url="{{ route('tasks.dependencies.store', $taskModel) }}"
        data-delete-url-template="{{ route('tasks.dependencies.destroy', [$taskModel, '__ID__']) }}"
        data-initial-dependencies='@json($initialDependencies)'
        data-can-manage="{{ $canManageDependencies ? '1' : '0' }}"
    >
        <div class="space-y-6">
            <div class="space-y-4">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <h4 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-diagram-2 text-slate-400"></i> Predecessores
                    </h4>
                    @if ($canManageDependencies && $availableTasks->isNotEmpty())
                        <form class="flex flex-col gap-2 sm:flex-row sm:items-center"
                              data-dependency-form
                              data-type="predecessor"
                              method="POST"
                              action="{{ route('tasks.dependencies.store', $taskModel) }}">
                            @csrf
                            <input type="hidden" name="type" value="predecessor">
                            <select name="task_id"
                                    class="rounded-2xl border border-slate-200 px-3 py-1.5 text-sm"
                                    required>
                                <option value="">Seleciona uma tarefa</option>
                                @foreach ($availableTasks as $candidate)
                                    <option value="{{ $candidate->task_id }}">{{ $candidate->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-atlas-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-atlas-600">
                                <i class="bi bi-plus-circle mr-1"></i> Adicionar
                            </button>
                        </form>
                    @endif
                </div>

                <ul class="space-y-3 text-sm text-slate-700" data-dependency-list="predecessor">
                    @forelse ($initialDependencies['predecessors'] as $dependency)
                        <li class="flex items-center justify-between rounded-2xl border border-slate-100 px-3 py-2 bg-slate-50"
                            data-dependency-id="{{ $dependency['id'] }}"
                            data-dependency-role="predecessor">
                            <div>
                                <a href="{{ route('tasks.show', $dependency['task_id']) }}" class="font-medium text-atlas-700 hover:text-atlas-900">
                                    {{ $dependency['name'] }}
                                </a>
                                <p class="text-xs text-slate-500">Estado: {{ $dependency['status'] ?? '—' }}</p>
                            </div>
                            @if ($canManageDependencies)
                                <form method="POST"
                                      action="{{ route('tasks.dependencies.destroy', [$taskModel, $dependency['id']]) }}"
                                      data-dependency-delete-form
                                      data-dependency-role="predecessor"
                                      data-dependency-id="{{ $dependency['id'] }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-sm text-rose-500 hover:text-rose-600"
                                            data-remove-dependency="{{ $dependency['id'] }}"
                                            data-dependency-role="predecessor">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-slate-500" data-empty-predecessors>Nenhum predecessor definido.</li>
                    @endforelse
                </ul>
            </div>

            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <h4 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-diagram-3 text-slate-400"></i> Sucessores
                    </h4>
                    @if ($canManageDependencies && $availableTasks->isNotEmpty())
                        <form class="flex flex-col gap-2 sm:flex-row sm:items-center"
                              data-dependency-form
                              data-type="successor"
                              method="POST"
                              action="{{ route('tasks.dependencies.store', $taskModel) }}">
                            @csrf
                            <input type="hidden" name="type" value="successor">
                            <select name="task_id"
                                    class="rounded-2xl border border-slate-200 px-3 py-1.5 text-sm"
                                    required>
                                <option value="">Seleciona uma tarefa</option>
                                @foreach ($availableTasks as $candidate)
                                    <option value="{{ $candidate->task_id }}">{{ $candidate->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-atlas-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-atlas-600">
                                <i class="bi bi-plus-circle mr-1"></i> Adicionar
                            </button>
                        </form>
                    @endif
                </div>

                <ul class="space-y-3 text-sm text-slate-700" data-dependency-list="successor">
                    @forelse ($initialDependencies['successors'] as $dependency)
                        <li class="flex items-center justify-between rounded-2xl border border-slate-100 px-3 py-2 bg-slate-50"
                            data-dependency-id="{{ $dependency['id'] }}"
                            data-dependency-role="successor">
                            <div>
                                <a href="{{ route('tasks.show', $dependency['task_id']) }}" class="font-medium text-atlas-700 hover:text-atlas-900">
                                    {{ $dependency['name'] }}
                                </a>
                                <p class="text-xs text-slate-500">Estado: {{ $dependency['status'] ?? '—' }}</p>
                            </div>
                            @if ($canManageDependencies)
                                <form method="POST"
                                      action="{{ route('tasks.dependencies.destroy', [$taskModel, $dependency['id']]) }}"
                                      data-dependency-delete-form
                                      data-dependency-role="successor"
                                      data-dependency-id="{{ $dependency['id'] }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-sm text-rose-500 hover:text-rose-600"
                                            data-remove-dependency="{{ $dependency['id'] }}"
                                            data-dependency-role="successor">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-slate-500" data-empty-successors>Nenhum sucessor definido.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
