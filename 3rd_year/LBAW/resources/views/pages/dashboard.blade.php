@extends('layouts.dashboard')

@section('title', 'Atlas · Dashboard')

@section('content')
<div class="flex min-h-screen">
  
    @include('partials.sidebar', ['user' => $user])

    {{-- Dashboard --}}
    <div class="flex-1">

        {{-- Header --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between px-4 py-6 sm:px-8 lg:px-12">
            <div>
                <p class="text-sm text-slate-500">Bem-vindo de volta, {{ $user->username }}!</p>
                <h1 class="mt-1 text-3xl font-semibold text-slate-900">
                    Aqui está o que está a acontecer hoje.
                </h1>
            </div>

            <label for="dashboardSearch" class="sr-only">Pesquisar no dashboard</label>
            <div class="flex w-full max-w-md items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="m21 21-4.35-4.35M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" />
                </svg>
                <input type="text"
                       id="dashboardSearch"
                       placeholder="Pesquisar projetos ou tarefas"
                       class="w-full border-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0">
            </div>
        </div>

        {{-- Search Results --}}
        <div id="searchResults" class="px-4 py-6 sm:px-8 lg:px-12 hidden"></div>

        {{-- Dashboard Content --}}
        <main id="dashboardContent" class="px-4 py-6 sm:px-8 lg:px-12">
            @include('partials.dashboard.detail_cards', ['stats' => $stats])
            @include('partials.dashboard.active_projects_card', ['projects' => $projects])
            @include('partials.dashboard.recent_tasks', ['recentTasks' => $recentTasks])
        </main>

    </div>
</div>
@endsection
