@extends('layouts.dashboard')

@section('title', 'Atlas · Projetos')

@section('content')
    <div class="flex min-h-screen">
        @include('partials.sidebar', ['user' => $user])

        <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12">
            @include('partials.modals.new_project_modal')

            <div class="flex flex-col gap-6">

                {{-- Header --}}
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Central de projetos</p>
                        <h1 class="text-3xl font-semibold text-slate-900">
                            Todos os projetos em andamento
                        </h1>
                    </div>

                    {{-- Create --}}
                    <form method="GET" action="{{ route('projects') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <label for="projects-search" class="sr-only">Pesquisar projetos</label>
                        <input
                            type="text"
                            id="projects-search"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            data-project-search
                            placeholder="Pesquisar projetos..."
                            class="w-full rounded-2xl border px-4 py-2 sm:w-72"
                        />

                        <label for="projects-sort" class="sr-only">Ordenar projetos</label>
                        <select id="projects-sort" name="sort" class="h-12 rounded-2xl border px-4">
                            <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Mais recentes</option>
                            <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Mais antigos</option>
                            <option value="favorites" @selected(($filters['sort'] ?? '') === 'favorites')>Favoritos</option>
                            <option value="archived" @selected(($filters['sort'] ?? '') === 'archived')>Arquivados</option>
                        </select>

                        <button type="submit" class="h-12 rounded-2xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Aplicar
                        </button>

                        <button
                            type="button"
                            data-open-modal="create-modal"
                            id="addMemberBtn"
                            class="inline-flex h-12 items-center justify-center rounded-2xl bg-atlas-500 px-6 text-sm font-semibold text-white transition hover:bg-atlas-900">
                            Adicionar projeto
                        </button>
                    </form>
                </div>

                @if (session('success'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                <div id="projectsGrid" class="{{ $projects->isEmpty() ? 'hidden' : '' }}">
                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($projects as $project)
                            @include('partials.projects.project_card', ['project' => $project])
                        @endforeach
                    </div>
                </div>

                <div id="emptyState" class="{{ $projects->isEmpty() ? '' : 'hidden' }}">
                    <div class="mt-12 flex flex-col items-center justify-center rounded-3xl border border-slate-200 bg-white py-16 text-center">
                        <h2 class="text-xl font-semibold text-slate-800">Ainda não há projetos</h2>

                        @if ($filters['search'])
                            <p class="mt-2 max-w-md text-sm text-slate-500">
                                Nenhum projeto corresponde à pesquisa "<strong>{{ $filters['search'] }}</strong>".
                                Tenta ajustar os filtros ou criar um novo projeto.
                            </p>
                        @else
                            <p class="mt-2 max-w-md text-sm text-slate-500">
                                Ainda não tens projetos associados. Clica em <strong>"Adicionar projeto"</strong> para criar o teu primeiro projeto!
                            </p>
                        @endif
                    </div>
                </div>

            </div>
        </main>
    </div>
@endsection
