@php
    $listOptions = $taskListOptions ?? collect();
    if (! $listOptions instanceof \Illuminate\Support\Collection) {
        $listOptions = collect($listOptions);
    }
@endphp
<div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between ">
     <a href="{{ route('tasks.show', $task['id']) }}" class="flex-1 block">
        <div class="space-y-1">
            <div class="flex items-center gap-2">

                {{-- Status dot --}}
                <span class="inline-flex h-2 w-2 rounded-full
                    {{ $task['status'] === 'Done' ? 'bg-emerald-500' :
                    ($task['status'] === 'InProgress' ? 'bg-amber-500' : 'bg-slate-400') }}">
                </span>

                <p class="font-semibold text-slate-900">{{ $task['name'] }}</p>

                {{-- Priority --}}
                <span class="rounded-full px-2 py-0.5 text-xs font-semibold
                    {{ $task['priority'] === 'Urgent' ? 'bg-rose-50 text-rose-600' :
                    ($task['priority'] === 'High' ? 'bg-amber-50 text-amber-600' :
                    ($task['priority'] === 'Medium' ? 'bg-sky-50 text-sky-600' :
                    'bg-emerald-50 text-emerald-600')) }}">
                    {{ $task['priority'] }}
                </span>
            </div>

            <p class="text-sm text-slate-500">{{ $task['description'] }}</p>

            <p class="text-xs text-slate-400">
                {{ $task['due_at'] ? 'Prazo: ' . $task['due_at'] : 'Sem prazo definido' }}
            </p>
            <p class="text-xs text-slate-400">
                <strong>Responsável:</strong> {{ $task['responsible_name'] ?? '—' }}
            </p>
            @if (!empty($task['task_list_name']))
                <p class="text-xs text-slate-400">
                    <strong>Lista:</strong> {{ $task['task_list_name'] }}
                </p>
            @endif

            @if ($task['assignee_name'])
                <p class="text-xs text-slate-400">
                    <strong>Atribuída a:</strong> {{ $task['assignee_name'] }}
                </p>
            @endif

            @if (($task['is_coordinator'] ?? false) && isset($project) && $listOptions->isNotEmpty())
                <form method="POST"
                      action="{{ route('projects.task-lists.assign-card', [$project, $task['id']]) }}"
                      class="mt-2 flex items-center gap-2 text-xs">
                    @csrf
                    <select name="task_list_id"
                            class="rounded-2xl border border-slate-200 px-3 py-1.5 text-xs">
                        <option value="">Sem lista</option>
                        @foreach ($listOptions as $option)
                            <option value="{{ $option['id'] }}"
                                @selected($task['task_list_id'] === $option['id'])>
                                {{ $option['name'] }}
                                @if (!empty($option['group']))
                                    — {{ $option['group'] }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="rounded-2xl bg-atlas-500 px-3 py-1 text-white hover:bg-atlas-600">
                        Atualizar
                    </button>
                </form>
            @endif
        </div>
    </a>

    <div class="flex items-center gap-3">

        @if ($task['can_edit'])
            {{-- Editable --}}
            <form method="POST"
                  action="{{ route('tasks.update', $task['id']) }}"
                  class="hidden"
                  data-task-update-form="{{ $task['id'] }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="">
            </form>

            <div class="rounded-full border border-slate-200 px-3 py-1">
                <select data-task-status="{{ $task['id'] }}"
                        class="border-0 bg-transparent text-sm text-slate-700 focus:outline-none">
                    @foreach (['Untouched', 'InProgress', 'Done'] as $status)
                        <option value="{{ $status }}" @selected($task['status'] === $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

        @else
            <span class="rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-400">
                {{ $task['status'] }}
            </span>
        @endif

    </div>
</div>
