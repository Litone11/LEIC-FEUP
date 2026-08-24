@extends('layouts.dashboard')

@section('title', 'Atlas · Tarefas')

@section('content')
<div class="flex min-h-screen">
    @include('partials.sidebar', ['user' => $user, 'project' => $project])

    <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12 space-y-12">
     <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-slate-900">Tarefas do Projeto: {{ $project->name }}</h1>
                     <button
                type="button"
                data-open-modal="task-modal"
                class="inline-flex items-center justify-center rounded-2xl bg-atlas-500 px-4 py-2 text-sm font-semibold text-white hover:bg-atlas-900"
            >
                Nova tarefa
            </button>
    </div>    

        {{-- ======================================
            TASK GROUPS & LISTS (COORDINATOR)
        ======================================= --}}
        @if ($isCoordinator || $taskGroups->isNotEmpty())
            @include('partials.tasks.task_groups', [
                'taskGroups'      => $taskGroups,
                'project'         => $project,
                'assignableTasks' => $assignableTasks,
                'user'            => $user,
                'userRole'        => $userRole,
                'isCoordinator'   => $isCoordinator,
            ])
        @endif

        {{-- ======================================
            MY TASKS (NON-coordinators only)
        ======================================= --}}
        @if ($userRole !== 'coordinator')
            <section class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-slate-900">As minhas tarefas</h2>
                </div>

                <div class="mt-3 divide-y divide-slate-200 rounded-3xl border border-slate-100 bg-white">
                    @forelse ($myTasks as $task)
                        @include('partials.tasks.task_card', [
                            'task' => $task,
                            'canEdit' => $task['can_edit'],
                            'project' => $project,
                            'taskListOptions' => $taskListOptions,
                        ])
                    @empty
                        <p class="px-6 py-8 text-center text-sm text-slate-500">
                            Não tens nenhuma tarefa atribuída.
                        </p>
                    @endforelse
                </div>
            </section>
        @endif


        {{-- ======================================
            TASKS WHERE I AM RESPONSIBLE
        ======================================= --}}
        @if ($userRole !== 'coordinator' && $responsibleTasks->isNotEmpty())
            <section class="space-y-6">
                <h2 class="text-2xl font-semibold text-slate-900">Tarefas onde sou responsável</h2>

                <div class="mt-3 divide-y divide-slate-200 rounded-3xl border border-slate-100 bg-white">
                    @foreach ($responsibleTasks as $task)
                        @include('partials.tasks.task_card', [
                            'task' => $task,
                            'canEdit' => $task['can_edit'],
                            'project' => $project,
                            'taskListOptions' => $taskListOptions,
                        ])
                    @endforeach
                </div>
            </section>
        @endif


        {{-- ======================================
            ALL TASKS 
        ======================================= --}}
        <section class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Todas as tarefas do projeto</h2>
                    <p class="text-sm text-slate-500">Pesquisa e organiza as tarefas para encontrares rapidamente o que precisas.</p>
                </div>

                <form method="GET"
                      action="{{ route('tasks.index', $project) }}"
                      class="flex flex-wrap gap-3 rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm"
                      data-task-filter-form>
                    <fieldset class="flex flex-wrap gap-3 border-0 p-0 m-0 min-w-0">
                        <legend class="sr-only">Filtros de tarefas</legend>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-slate-500" for="task-search-all">Pesquisar</label>
                            <input type="text"
                                   id="task-search-all"
                                   name="search_all"
                                   value="{{ $searchAll }}"
                                   placeholder="Ex: login, urgente, responsável"
                                   class="rounded-2xl border border-slate-200 px-3 py-1.5 text-sm focus:border-atlas-300 focus:ring-0">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-slate-500" for="task-sort-all">Ordenar por</label>
                            <select id="task-sort-all" name="sort_all"
                                    class="rounded-2xl border border-slate-200 px-3 py-1.5 text-sm focus:border-atlas-300 focus:ring-0">
                                <option value="due_at" @selected($taskSortField === 'due_at')>Prazo</option>
                                <option value="priority" @selected($taskSortField === 'priority')>Prioridade</option>
                                <option value="status" @selected($taskSortField === 'status')>Estado</option>
                                <option value="name" @selected($taskSortField === 'name')>Nome</option>
                                <option value="created_at" @selected($taskSortField === 'created_at')>Data de criação</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-slate-500" for="task-sort-dir">Ordem</label>
                            <select id="task-sort-dir" name="sort_dir"
                                    class="rounded-2xl border border-slate-200 px-3 py-1.5 text-sm focus:border-atlas-300 focus:ring-0">
                                <option value="asc" @selected($taskSortDirection === 'asc')>Ascendente</option>
                                <option value="desc" @selected($taskSortDirection === 'desc')>Descendente</option>
                            </select>
                        </div>
                    </fieldset>
                    <div class="flex items-end">
                        <button type="submit"
                                class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Aplicar filtros
                        </button>
                    </div>
                </form>
            </div>

            <div id="allTasksList">
                @include('partials.tasks.tasks_list', [
                    'project' => $project,
                    'taskListOptions' => $taskListOptions,
                ])
            </div>
        </section>

    </main>
</div>

@include('partials.modals.new_task_modal', ['project' => $project])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-task-filter-form]');
    const listContainer = document.getElementById('allTasksList');
    if (!form || !listContainer) return;

    const submitFilters = () => {
        const params = new URLSearchParams(new FormData(form));
        params.set('only_tasks', '1');

        const url = `${form.action}?${params.toString()}`;
        listContainer.classList.add('opacity-50');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (!res.ok) throw new Error('Erro ao carregar as tarefas.');
                return res.text();
            })
            .then(html => {
                listContainer.innerHTML = html;
                listContainer.classList.remove('opacity-50');

                // Atualiza histórico sem only_tasks
                params.delete('only_tasks');
                const niceUrl = `${form.action}?${params.toString()}`;
                window.history.replaceState({}, '', niceUrl);
            })
            .catch(error => {
                console.error(error);
                listContainer.classList.remove('opacity-50');
                if (window.showFlashToast) {
                    window.showFlashToast({
                        message: error.message || 'Não foi possível aplicar os filtros.',
                        type: 'error'
                    });
                }
            });
    };

    const autoSubmitFields = form.querySelectorAll('select[name="sort_all"], select[name="sort_dir"]');
    autoSubmitFields.forEach(field => {
        field.addEventListener('change', () => submitFilters());
    });

    const searchInput = form.querySelector('input[name="search_all"]');
    let debounceId;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceId);
            debounceId = setTimeout(() => submitFilters(), 300);
        });
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        submitFilters();
    });
});
</script>
@endpush
