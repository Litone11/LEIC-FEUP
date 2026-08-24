@extends('layouts.dashboard')

@section('content')
<div class="flex min-h-screen">
    @include('partials.sidebar', ['user' => $user, 'project' => $project])

    <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12 space-y-8">

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <a href="{{ route('projects.show', $task['project']->project_id) }}"
                   class="inline-flex items-center text-sm text-slate-500 hover:text-slate-700">
                    <i class="bi bi-arrow-left mr-1"></i> Voltar ao projeto
                </a>
                <h1 class="mt-2 text-3xl font-bold text-slate-900 break-words">
                    {{ $task['name'] }}
                </h1>
                <p class="text-sm text-slate-500">Estado atual: {{ $task['status'] }} · Prioridade {{ $task['priority'] }}</p>
            </div>

        </div>

        {{-- Main layout --}}
        <section class="grid gap-8 lg:grid-cols-[2fr,1fr]">
            <div class="space-y-6">
                @include('partials.tasks.task_details', ['task' => $task])
                @include('partials.tasks.edit_form', ['task' => $task, 'project' => $project])
               
            </div>

            <div class="space-y-6">
                
                @include('partials.tasks.task_comments', [
                    'taskModel'       => $taskModel,
                    'task'            => $task,
                    'initialComments' => $initialComments,
                ])
                 @include('partials.tasks.task_dependencies', [
                    'taskModel'             => $taskModel,
                    'task'                  => $task,
                    'initialDependencies'   => $initialDependencies,
                    'availableTasks'        => $availableTasks,
                    'canManageDependencies' => $canManageDependencies,
                ])
            </div>
        </section>

    </main>
</div>

@endsection

@include('partials.modals.task_delete_modal', ['task' => $task])