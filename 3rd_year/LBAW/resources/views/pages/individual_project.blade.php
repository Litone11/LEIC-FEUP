@extends('layouts.dashboard')

@section('title', 'Atlas · ' . $project->name)

@section('content')
<div class="flex min-h-screen">
    @include('partials.sidebar', ['user' => $user, 'project' => $project])

    <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12 space-y-8">

        {{-- General Project Info --}}
        @include('partials.projects.project_info_card', [
            'project' => $project,
            'summary' => $summary
        ])


        {{-- ================================
            GRID LAYOUT: Tasks + Right Column
        ================================== --}}
        <section class="grid gap-8 lg:grid-cols-[2fr,1fr]">

            {{-- ---- LEFT : TASKS ---- --}}
            <div>

                @php
                    // Filter visible tasks depending on role:
                    $visibleTasks = $allTasks->filter(function ($t) use ($user, $userRole) {
                        if ($userRole === 'coordinator') {
                            return true; // coordinator sees all
                        }

                        return $t['assignee_id'] === $user->user_id
                            || $t['task_responsible_id'] === $user->user_id;
                    });
                @endphp

                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">
                        {{ $userRole === 'coordinator' ? 'As tarefas do projeto' : 'As minhas tarefas' }}
                    </h2>
                    <span class="text-sm text-slate-500">{{ $visibleTasks->count() }} visíveis</span>
                </div>

                <div class="mt-4 divide-y divide-slate-200 rounded-3xl border border-slate-100 bg-white">

                    @if ($allTasks->isEmpty())
                        <p class="px-6 py-8 text-center text-sm text-slate-500">
                            Nenhuma tarefa associada ainda.
                        </p>

                    @elseif ($visibleTasks->isEmpty())
                        <p class="px-6 py-8 text-center text-sm text-slate-500">
                            Nenhuma tarefa disponível.
                        </p>

                    @else
                        @foreach ($visibleTasks as $task)
                            @include('partials.tasks.task_card', [
                                'task' => $task,
                                'canEdit' => $task['can_edit']
                            ])
                        @endforeach
                    @endif

                </div>
            </div>

            <div class="space-y-6">

                @include('partials.projects.project_cronogram_card', [
                    'summary' => $summary
                ])

                @include('partials.projects.members_card', [
                    'members' => $members,
                    'project' => $project,
                    'userRole' => $userRole
                ])

            </div>

        </section>
    </main>
</div>
@endsection
