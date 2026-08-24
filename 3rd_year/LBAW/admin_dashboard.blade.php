@extends('layouts.dashboard')

@section('title', 'Atlas · Admin Dashboard')

@section('content')
<div class="flex min-h-screen">
    <script>
        window.updateUserRoute = "{{ route('admin.users.update') }}";
        window.blockUserRouteTemplate = "{{ route('admin.users.block', ['user' => '__USER__']) }}";
        window.unblockUserRouteTemplate = "{{ route('admin.users.unblock', ['user' => '__USER__']) }}";
        window.deleteUserRouteTemplate = "{{ route('admin.users.destroy', ['user' => '__USER__']) }}";
        window.csrfToken = "{{ csrf_token() }}";
    </script>
          @include('partials.admin.admin_sidebar', ['user' => $user, 'activeAdminTab' => $activeTab])
    <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-6">
            <div>
                <h1 class="mt-1 text-3xl font-semibold text-slate-900">Painel de Administração</h1>
                <p class="text-sm text-slate-500">Monitoriza projetos e utilizadores a partir deste painel.</p>
            </div>
            <div class="rounded-full border border-slate-200 bg-slate-50 p-1 inline-flex gap-1">
                <a href="{{ route('admin.dashboard', ['tab' => 'projects']) }}"
                   class="rounded-full px-4 py-2 text-sm font-semibold transition
                        {{ $activeTab === 'projects'
                            ? 'bg-white text-atlas-600 shadow'
                            : 'text-slate-500 hover:text-slate-800' }}">
                    Projetos
                </a>
                <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}"
                   class="rounded-full px-4 py-2 text-sm font-semibold transition
                        {{ $activeTab === 'users'
                            ? 'bg-white text-atlas-600 shadow'
                            : 'text-slate-500 hover:text-slate-800' }}">
                    Utilizadores
                </a>
            </div>
        </div>

        @php
            $taskCompletionRate = $stats['total_tasks'] > 0
                ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100)
                : 0;
            $blockedRate = $stats['total_users'] > 0
                ? round(($stats['blocked_users'] / $stats['total_users']) * 100)
                : 0;
        @endphp

        {{-- Estatísticas principais --}}
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 mb-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Projetos totais</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['total_projects'] }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $stats['active_projects'] }} ativos</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Projetos ativos</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['active_projects'] }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $stats['archived_projects'] }} arquivados</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Utilizadores</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['total_users'] }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $stats['admin_users'] }} administradores</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Projetos suspensos</p>
                <p class="mt-3 text-3xl font-semibold text-amber-600">{{ $stats['suspended_projects'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Em revisão administrativa</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Utilizadores bloqueados</p>
                <p class="mt-3 text-3xl font-semibold text-rose-600">{{ $stats['blocked_users'] }}</p>
                <div class="mt-2">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>{{ $blockedRate }}%</span>
                        <span>da base</span>
                    </div>
                    <div class="mt-1 h-2 rounded-full bg-rose-100">
                        <div class="h-2 rounded-full bg-rose-500" style="width: {{ $blockedRate }}%"></div>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Tarefas registadas</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['total_tasks'] }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $stats['completed_tasks'] }} concluídas</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-semibold uppercase text-slate-500">Conclusão global</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $taskCompletionRate }}%</p>
                <div class="mt-2 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $taskCompletionRate }}%"></div>
                </div>
            </div>
        </section>

        @if ($activeTab === 'projects')
        {{-- Tabela de projetos--}}
        <section id="admin-projects-panel" class="rounded-2xl border border-slate-200 bg-white mb-8">
            <div class="flex justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="text-xl font-semibold text-slate-900">Projetos</h2>
                <span class="text-sm text-slate-500">{{ count($projects ?? []) }} registos</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wide">
                        <tr>
                            <th class="px-6 py-3 text-left">Nome</th>
                            <th class="px-6 py-3 text-left">Estado</th>
                            <th class="px-6 py-3 text-left">Progresso</th>
                            <th class="px-6 py-3 text-left">Equipa</th>
                            <th class="px-6 py-3 text-left">Criado em</th>
                            <th class="px-6 py-3 text-left">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        @php
                            $statusClass = match($project['status']) {
                                'Completed' => 'bg-emerald-50 text-emerald-600',
                                'In Progress' => 'bg-amber-50 text-amber-600',
                                'Suspended' => 'bg-rose-50 text-rose-600',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <tr class="border-b border-slate-50">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $project['name'] }}</td>
                            <td class="px-6 py-4">
                                <span class="{{ $statusClass }} inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold">
                                    {{ $project['status'] }}
                                </span>
                                @if($project['is_suspended'] && $project['suspension_reason'])
                                    <p class="mt-1 text-xs text-rose-500">Razão: {{ $project['suspension_reason'] }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>{{ $project['progress'] }}%</span>
                                        <span>
                                            @if($project['is_suspended'])
                                                Suspenso
                                            @elseif($project['is_archived'])
                                                Arquivado
                                            @else
                                                Ativo
                                            @endif
                                        </span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100">
                                        <div class="h-2 rounded-full bg-atlas-500" style="width: {{ $project['progress'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">{{ $project['members'] }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ $project['created_at'] }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.projects.show', $project['id']) }}"
                                       class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-atlas-200 hover:bg-atlas-50 hover:text-atlas-600">
                                        Ver detalhes
                                    </a>
                                    @if(!$project['is_suspended'])
                                    <button type="button"
                                            class="rounded-xl border border-amber-200 px-4 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50 open-suspend-modal"
                                            data-suspend-route="{{ route('admin.projects.suspend', $project['id']) }}"
                                            data-project-name="{{ $project['name'] }}">
                                        Suspender
                                    </button>
                                    @else
                                    <form method="POST"
                                          action="{{ route('admin.projects.unsuspend', $project['id']) }}"
                                          data-admin-action="unsuspend-project"
                                          data-success-message="Projeto reativado com sucesso."
                                          data-error-message="Não foi possível reativar o projeto."
                                          data-reload="true">
                                        @csrf
                                        <button class="rounded-xl border border-emerald-200 px-4 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                            Reativar
                                        </button>
                                    </form>
                                    @endif
                                    <form method="POST"
                                          action="{{ route('admin.projects.destroy', $project['id']) }}"
                                          data-admin-action="delete-project"
                                          data-success-message="Projeto eliminado com sucesso."
                                          data-error-message="Não foi possível eliminar o projeto."
                                          data-reload="true"
                                          onsubmit="return confirm('Eliminar o projeto e todo o seu conteúdo? Esta ação é irreversível.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-xl border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">Sem projetos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @else
        {{-- Tabela de users--}}
        <section class="rounded-3xl border border-slate-200 bg-white">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="text-xl font-semibold text-slate-900">Todos os utilizadores</h2>
                <div class="flex gap-2 mt-2 sm:mt-0">
                    <input type="text" id="userSearch" placeholder="Pesquisar..."
                        class="w-64 rounded-xl border px-4 py-2 text-sm text-slate-700">
                    <button type="button" data-open-modal="addNewAccount" id="addNewAccount" 
                            class="rounded-2xl bg-atlas-500 text-white px-4 py-2 hover:bg-atlas-600">
                        Adicionar Conta
                    </button>
                </div>
            </div>


            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm" id="usersTable">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wide">
                        <tr>
                            <th class="px-6 py-3 text-left">Username</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-left">Admin</th>
                            <th class="px-6 py-3 text-left">Estado</th>
                            <th class="px-6 py-3 text-left">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                      <tr class="user-row cursor-pointer"
                            data-id="{{ $user['id'] }}"
                            data-username="{{ $user['username'] }}"
                            data-email="{{ $user['email'] }}"
                            data-is-admin="{{ $user['is_admin'] ? '1' : '0' }}"
                            data-blocked="{{ $user['blocked'] ? '1' : '0' }}"
                            data-block-reason="{{ $user['block_reason'] ?? '' }}"
                            data-deleted="{{ $user['deleted'] ? '1' : '0' }}">
                            <td class="px-6 py-4">{{ $user['username'] }}</td>
                            <td class="px-6 py-4">{{ $user['email'] }}</td>
                            <td class="px-6 py-4">
                                <span class="{{ $user['is_admin'] ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold">
                                    {{ $user['is_admin'] ? 'Sim' : 'Não' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusMeta = [
                                        'label' => 'Active',
                                        'classes' => 'bg-emerald-50 text-emerald-600',
                                    ];
                                    if ($user['deleted']) {
                                        $statusMeta = ['label' => 'Deleted', 'classes' => 'bg-slate-200 text-slate-700'];
                                    } elseif ($user['blocked']) {
                                        $statusMeta = ['label' => 'Blocked', 'classes' => 'bg-rose-50 text-rose-600'];
                                    }
                                @endphp
                                <span data-role="status-badge" class="{{ $statusMeta['classes'] }} inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold">
                                    {{ $statusMeta['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button type="button"
                                        class="edit-user-btn rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-atlas-200 hover:bg-atlas-50 hover:text-atlas-600"
                                        data-open-modal="admin-user-modal">
                                    Editar
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        @if(empty($users))
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No users found.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
        {{-- "form"  para editar users --}}
       @includeWhen($activeTab === 'users', 'partials.modals.admin_user_modal')
        @endif

    </main>
</div>


@endsection

@include('partials.modals.admin_suspend_modal')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('admin_suspend_modal');
    if (!modal) return;

    const form = modal.querySelector('form');
    const reasonInput = modal.querySelector('#suspendReasonInput');
    const projectNameEl = modal.querySelector('[data-project-name]');

    document.querySelectorAll('.open-suspend-modal').forEach(button => {
        button.addEventListener('click', () => {
            form.action = button.dataset.suspendRoute;
            projectNameEl.textContent = button.dataset.projectName || 'este projeto';
            reasonInput.value = '';

            if (window.open_admin_suspend_modal) {
                window.open_admin_suspend_modal();
            }
        });
    });

    form.addEventListener('submit', event => {
        if (!reasonInput.value.trim()) {
            event.preventDefault();
            alert('Indica uma razão para suspender o projeto.');
        }
    });
});
</script>
@endpush
