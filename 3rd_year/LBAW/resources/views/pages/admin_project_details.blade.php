@extends('layouts.dashboard')

@section('title', 'Atlas · Projeto ' . $project->name)

@section('content')
<div class="min-h-screen bg-atlas-50">
    <div class="px-4 sm:px-8 lg:px-12 space-y-8">
        <div class="sticky top-0 z-20 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-atlas-50/95 px-4 py-4 backdrop-blur sm:px-0">
            <a href="{{ route('home') }}" class="text-2xl font-semibold text-slate-900">Atlas</a>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('profile') }}"
                   class="rounded-full border border-slate-200 px-4 py-2 font-semibold text-atlas-600 hover:bg-atlas-50">
                    Perfil
                </a>
                <a href="{{ route('logout') }}"
                   class="rounded-full border border-slate-200 px-4 py-2 font-semibold text-slate-500 hover:bg-slate-100">
                    Terminar sessão
                </a>
            </div>
        </div>
        <div class="flex items-center justify-between text-sm text-slate-500">
            <a href="{{ route('admin.dashboard', ['tab' => 'projects']) }}" class="inline-flex items-center gap-2 hover:text-atlas-600">
                <span>&larr;</span> Voltar ao painel
            </a>
            <span>ID do projeto: {{ $project->project_id }}</span>
        </div>

        @if ($project->isSuspended())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <div class="flex flex-col gap-1">
                    <p class="font-semibold">Projeto suspenso</p>
                    @if ($project->suspensionReason())
                        <p>Razão: {{ $project->suspensionReason() }}</p>
                    @endif
                    <p>Os membros não podem aceder enquanto estiver suspenso.</p>
                </div>
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-500">Projeto</p>
                    <h1 class="mt-1 text-3xl font-semibold text-slate-900">{{ $project->name }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ $project->description }}</p>
                </div>
                @php
                    $statusBadges = [
                        'Completed' => 'bg-emerald-50 text-emerald-600',
                        'In Progress' => 'bg-amber-50 text-amber-600',
                        'Planning' => 'bg-slate-100 text-slate-600',
                        'Suspended' => 'bg-rose-50 text-rose-600',
                    ];
                    $statusClass = $statusBadges[$project->status_label] ?? 'bg-slate-100 text-slate-600';
                @endphp
                <div class="text-right space-y-2">
                    <span class="{{ $statusClass }} inline-flex items-center rounded-full px-4 py-1 text-xs font-semibold">
                        {{ $project->status_label }}
                    </span>
                    <div class="text-xs text-slate-500 space-y-1">
                        <p><strong>Criado em:</strong> {{ $project->formatted_date ?? $project->created_at?->format('M d, Y') }}</p>
                        <p><strong>Estado:</strong> {{ $project->is_archived ? 'Arquivado' : 'Ativo' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Progresso</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['progress'] }}%</p>
                <div class="mt-3 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-atlas-500" style="width: {{ $summary['progress'] }}%"></div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Tarefas</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['tasks_ratio'] }}</p>
                <p class="text-xs text-slate-500">Concluídas / Total</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Data Inicial</p>
                <p class="mt-2 text-xl font-semibold text-slate-900">{{ $summary['start_date'] ?? 'Sem data' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Equipa</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['team_count'] }}</p>
                <p class="text-xs text-slate-500">Membros ativos</p>
            </div>
        </section>

        <section class="grid gap-8 lg:grid-cols-[1.2fr,1fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Equipa do Projeto</h2>
                    <span class="text-sm text-slate-500">{{ $members->count() }} utilizadores</span>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left">Nome</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Papel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $member)
                            <tr class="border-b border-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $member['username'] }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $member['email'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $member['role'] === 'coordinator' ? 'bg-atlas-50 text-atlas-600' : 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($member['role']) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-500">Nenhum membro associado.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Tarefas recentes</h2>
                    <span class="text-sm text-slate-500">{{ $recentTasks->count() }} registos</span>
                </div>

                <div class="mt-4 space-y-4">
                    @forelse($recentTasks as $task)
                    <article class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-semibold text-slate-900">{{ $task['name'] }}</h3>
                            <span class="text-xs font-semibold rounded-full px-3 py-1
                                {{ match($task['status']) {
                                    'Done' => 'bg-emerald-100 text-emerald-700',
                                    'In Progress' => 'bg-amber-100 text-amber-700',
                                    'Planning', 'To Do' => 'bg-slate-200 text-slate-700',
                                    default => 'bg-slate-200 text-slate-700'
                                } }}">
                                {{ $task['status'] }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">
                            Responsável: <strong>{{ $task['responsible'] ?? '—' }}</strong> ·
                            Executor: <strong>{{ $task['assignee'] ?? '—' }}</strong>
                        </p>
                        <div class="mt-2 text-xs text-slate-500 flex flex-wrap gap-3">
                            <span>Criada: {{ $task['created_at'] ?? '—' }}</span>
                            <span>Prazo: {{ $task['due_at'] ?? '—' }}</span>
                            <span>Prioridade: {{ $task['priority'] ?? '—' }}</span>
                        </div>
                    </article>
                    @empty
                    <p class="text-sm text-slate-500">Sem tarefas registadas para este projeto.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 space-y-4">
                <h2 class="text-xl font-semibold text-slate-900">Suspender projeto</h2>
                @if (!$project->isSuspended())
                    <p class="text-sm text-slate-500">Suspende o projeto temporariamente. Os membros deixam de o ver até o reativares.</p>
                    <form method="POST"
                          action="{{ route('admin.projects.suspend', $project) }}"
                          class="space-y-3"
                          data-admin-action="suspend-project"
                          data-success-message="Projeto suspenso com sucesso."
                          data-error-message="Não foi possível suspender o projeto."
                          data-reload="true">
                        @csrf
                        <label class="block text-sm font-medium text-slate-700" for="suspend-reason">Razão</label>
                        <textarea id="suspend-reason" name="reason" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm" required placeholder="Ex: Conteúdo inapropriado detectado"></textarea>
                        <button type="submit" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100">
                            Suspender projeto
                        </button>
                    </form>
                @else
                    <p class="text-sm text-slate-500">Este projeto está suspenso. Reativa-o para devolver acesso aos membros.</p>
                    <form method="POST"
                          action="{{ route('admin.projects.unsuspend', $project) }}"
                          data-admin-action="unsuspend-project"
                          data-success-message="Projeto reativado com sucesso."
                          data-error-message="Não foi possível reativar o projeto."
                          data-reload="true">
                        @csrf
                        <button type="submit" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
                            Reativar projeto
                        </button>
                    </form>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 space-y-4">
                <h2 class="text-xl font-semibold text-rose-700">Eliminar projeto</h2>
                <p class="text-sm text-slate-500">Apaga definitivamente o projeto, as tarefas e estatísticas associadas. Esta ação não pode ser desfeita.</p>
                <form method="POST"
                      action="{{ route('admin.projects.destroy', $project) }}"
                      data-admin-action="delete-project"
                      data-success-message="Projeto eliminado com sucesso."
                      data-error-message="Não foi possível eliminar o projeto."
                      data-reload="true"
                      onsubmit="return confirm('Eliminar o projeto e todos os seus dados? Esta ação é irreversível.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                        Eliminar projeto
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
