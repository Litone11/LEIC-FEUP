<div
    id="forum-topic-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm"
>
    <div class="mx-auto w-full max-w-3xl">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Novo tópico</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Criar discussão no fórum</h2>
                </div>
                <button type="button" data-close-modal class="text-slate-400 hover:text-slate-700">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('projects.forum.store', $project) }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="text-sm font-semibold text-slate-700">Título</label>
                    <input
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        maxlength="120"
                        class="mt-1 w-full rounded-2xl border border-slate-300 px-4 py-2 text-sm"
                        required
                    >
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Mensagem</label>
                    <textarea
                        name="body"
                        rows="5"
                        maxlength="2000"
                        class="mt-1 w-full rounded-2xl border border-slate-300 px-4 py-2 text-sm"
                        required
                    >{{ old('body') }}</textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Relacionar com uma tarefa (opcional)</label>
                    <select
                        name="task_id"
                        class="mt-1 w-full rounded-2xl border border-slate-300 px-4 py-2 text-sm"
                    >
                        <option value="">— Sem ligação —</option>
                        @foreach ($tasks as $task)
                            <option value="{{ $task->task_id }}" @selected(old('task_id') == $task->task_id)>
                                {{ $task->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button"
                            data-close-modal
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-atlas-500 px-5 py-2 text-sm font-semibold text-white hover:bg-atlas-600">
                        Criar tópico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
