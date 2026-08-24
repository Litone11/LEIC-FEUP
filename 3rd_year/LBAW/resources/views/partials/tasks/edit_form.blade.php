@if ($task['can_edit'])
@php
    $wrapperClasses = $wrapperClasses ?? 'mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm';
@endphp
<div class="{{ trim($wrapperClasses) }}">

    <h3 class="text-lg font-semibold mb-4 text-slate-900">Editar tarefa</h3>

    <form method="POST"
          action="{{ route('tasks.update', $task['id']) }}"
          class="flex flex-col gap-4">
        @csrf
        @method('PATCH')

        {{-- Title --}}
        <div>
            <label class="text-sm text-slate-700">Título</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $task['name']) }}"
                placeholder="Ex: Rever copy da landing page"
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                required
            >
        </div>

        {{-- Description --}}
        <div>
            <label class="text-sm text-slate-700">Descrição</label>
            <textarea
                name="description"
                rows="3"
                placeholder="Resumo rápido do que é preciso fazer"
                class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                required
            >{{ old('description', $task['description']) }}</textarea>
        </div>

        {{-- Status --}}
        <div>
            <label class="text-sm text-slate-700">Estado</label>
            <select name="status" class="mt-1 w-full rounded-lg border-slate-300">
                @foreach(['Untouched', 'InProgress', 'Done'] as $status)
                    <option value="{{ $status }}" @selected($task['status'] === $status)>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Priority --}}
        <div>
            <label class="text-sm text-slate-700">Prioridade</label>
            <select name="priority" class="mt-1 w-full rounded-lg border-slate-300">
                @foreach(['Urgent','High','Medium','Low'] as $p)
                    <option value="{{ $p }}" @selected($task['priority'] === $p)>
                        {{ $p }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Deadline --}}
        <div>
            <label class="text-sm text-slate-700">Prazo</label>
            <input
                type="date"
                name="due_at"
                value="{{ old('due_at', $task['raw_due_at'] ?? '') }}"
                min="{{ now()->toDateString() }}"
                class="mt-1 w-full rounded-lg border-slate-300"
                required
            >
        </div>

        @if ($task['is_coordinator'])

            {{-- Responsible --}}
            <div>
                <label class="text-sm font-medium text-slate-700">Responsável</label>
                <select name="responsible" class="mt-1 w-full rounded-lg">
                    @foreach ($project->users->where('pivot.user_role', '!=', 'coordinator') as $member)
                        <option value="{{ $member->user_id }}"
                            @selected($task['task_responsible_id'] == $member->user_id)>
                            {{ $member->username }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Assignee --}}
            <div>
                <label class="text-sm font-medium text-slate-700">Atribuído a</label>
                <select name="assignee" class="mt-1 w-full rounded-lg">
                    <option value="">— Nenhum —</option>
                    @foreach ($project->users->where('pivot.user_role', '!=', 'coordinator') as $member)
                        <option value="{{ $member->user_id }}"
                            @selected($task['assignee_id'] == $member->user_id)>
                            {{ $member->username }}
                        </option>
                    @endforeach
                </select>
            </div>

        @elseif (!empty($task['is_responsible']))

            {{-- Assignee (responsável pode atribuir) --}}
            <div>
                <label class="text-sm font-medium text-slate-700">Atribuído a</label>
                <select name="assignee" class="mt-1 w-full rounded-lg">
                    <option value="">— Nenhum —</option>
                    @foreach ($project->users->where('pivot.user_role', '!=', 'coordinator') as $member)
                        <option value="{{ $member->user_id }}"
                            @selected($task['assignee_id'] == $member->user_id)>
                            {{ $member->username }}
                        </option>
                    @endforeach
                </select>
            </div>

        @endif

        {{-- Tags --}}
        <div>
            <label class="text-sm font-semibold text-slate-700 mb-2">Tags</label>
            <div class="flex flex-wrap gap-2 mb-2">
                @php
                    $taskTags = collect($task['tags']);
                @endphp

                @foreach ($project->tags as $tag)
                    @php
                        $selected = $taskTags->pluck('tag_id')->contains($tag->tag_id);
                    @endphp
                    <button type="button"
                        class="tag-toggle px-3 py-1 rounded-full border text-sm font-medium
                        {{ $selected ? 'bg-atlas-500 text-white border-atlas-600' : 'bg-gray-50 text-gray-700 border-gray-300' }}"
                        data-tag-id="{{ $tag->tag_id }}">
                        {{ $tag->name }}
                    </button>
                @endforeach
            </div>

            {{-- tags selecionadas --}}
            <input type="hidden" name="tags" id="task-tags-{{ $task['id'] }}" value="{{ $taskTags->pluck('tag_id')->implode(',') }}">
        </div>

        {{-- Save --}}
        <button
            type="submit"
            class="mt-4 rounded-full bg-atlas-500 px-6 py-2 text-white text-sm font-semibold hover:bg-atlas-600"
        >
            Guardar alterações
        </button>

        {{-- Delete --}}
        <button
            type="button"
            data-open-modal="delete-task-modal"
            class="mt-2 rounded-full bg-rose-600 px-6 py-2 text-white text-sm font-semibold hover:bg-rose-700"
        >
            Eliminar tarefa
        </button>

    </form>
</div>



@endif
