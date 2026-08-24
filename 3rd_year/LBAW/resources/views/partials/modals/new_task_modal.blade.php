<div id="task-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm px-4 sm:px-6">

    <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl">

        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Nova tarefa</p>
                <h2 class="text-xl font-semibold text-slate-900">Adicionar tarefa ao projeto</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="mt-6 space-y-4">
            @csrf

            <fieldset class="space-y-4 border-0 p-0 m-0">
                <legend class="sr-only">Detalhes da tarefa</legend>

                {{-- Título --}}
                <div>
                    <label class="text-sm font-medium text-slate-900">Título</label>
                    <input type="text"
                           name="name"
                           required
                           maxlength="80"
                           value="{{ old('name') }}"
                           class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 @error('name') border-rose-400 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição --}}
                <div>
                    <label class="text-sm font-medium text-slate-900">Descrição</label>
                    <textarea name="description"
                              rows="3"
                              maxlength="256"
                              required
                              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 @error('description') border-rose-400 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PRIORIDADE + PRAZO --}}
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-900">Prioridade</label>
                        <select name="priority"
                                class="mt-2 w-full rounded-2xl border px-4 py-2 @error('priority') border-rose-400 @enderror">
                            @foreach (['Urgent','High','Medium','Low'] as $priority)
                                <option value="{{ $priority }}"
                                    @selected(old('priority','Medium') === $priority)>
                                    {{ $priority }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-900">Prazo</label>
                        <input type="date"
                               name="due_at"
                               required
                               min="{{ now()->toDateString() }}"
                               value="{{ old('due_at') }}"
                               class="mt-2 w-full rounded-2xl border px-4 py-2 @error('due_at') border-rose-400 @enderror">
                        @error('due_at')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            {{-- ONLY FOR COORDINATOR --}}
            @if ($userRole === 'coordinator')
                <fieldset class="mt-4 space-y-4 border-0 p-0 m-0 pt-4 border-t border-slate-200">
                    <legend class="text-sm font-semibold text-slate-900 mb-1">Atribuições</legend>

                    {{-- RESPONSÁVEL --}}
                    <div>
                        <label class="text-sm font-medium text-slate-900">Responsável (email)</label>
                        <input type="email"
                               name="responsible"
                               placeholder="email do responsável"
                               class="mt-2 w-full rounded-2xl border px-4 py-2 @error('responsible') border-rose-400 @enderror"
                               value="{{ old('responsible') }}"
                               required>
                        @error('responsible')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ASSIGNADO --}}
                    <div>
                        <label class="text-sm font-medium text-slate-900">Atribuído (email)</label>
                        <input type="email"
                               name="assignee"
                               placeholder="email do atribuído (opcional)"
                               class="mt-2 w-full rounded-2xl border px-4 py-2 @error('assignee') border-rose-400 @enderror"
                               value="{{ old('assignee') }}">
                        @error('assignee')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                </fieldset>
            @endif

            {{-- BUTTONS --}}
            <div class="flex justify-end gap-2 pt-2">
                <button type="button"
                        data-close-modal
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold">
                    Cancelar
                </button>

                <button type="submit"
                        class="rounded-2xl bg-atlas-500 px-5 py-2 text-sm font-semibold text-white">
                    Guardar tarefa
                </button>
            </div>

        </form>
    </div>
</div>
