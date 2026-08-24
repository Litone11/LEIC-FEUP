@extends('layouts.dashboard')

@section('title', 'Atlas · Configurações · ' . $project->name)

@section('content')
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    @include('partials.sidebar', ['user' => $user, 'project' => $project])

    {{-- MAIN CONTENT --}}
<main class="flex-1 px-4 py-6 sm:px-8 lg:px-10 space-y-8">
  

        <div>
        <a href="{{ route('projects.show', $project) }}"
        class="inline-flex items-center gap-2 text-slate-500 hover:text-atlas-500">                
        <span>&larr;</span> Voltar à overview
            </a>
            <h1 class="text-2xl font-semibold text-slate-900">Configurações do Projeto</h1>
            <p class="text-slate-500">
                Edita o nome, a descrição do projeto
            </p>
        </div>


<div class="max-w-6xl mx-auto space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

<div class="lg:col-span-2 rounded-3xl bg-white shadow-sm border border-slate-200 p-6 space-y-6">
    @if (session('success'))
        <p class="text-green-600 text-sm font-semibold">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ route('projects.settings.update', $project) }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <!-- Project Name -->
        <div>
            <label class="text-sm font-medium text-slate-900">Nome do projeto</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $project->name) }}"
                class="mt-1 w-full rounded-2xl border border-slate-300 px-4 py-2 text-sm"
                required
            >
        </div>

        <!-- Description -->
        <div>
            <label class="text-sm font-medium text-slate-900">Descrição</label>
            <textarea
                name="description"
                rows="4"
                class="mt-1 w-full rounded-2xl border border-slate-300 px-4 py-2 text-sm"
                required
            >{{ old('description', $project->description) }}</textarea>
        </div>

        <!-- Color -->
        <div>
            <label class="text-sm font-medium text-slate-900">Cor do projeto</label>
            <div class="mt-2 flex items-center gap-4">
                <input
                    type="color"
                    name="color"
                    value="{{ old('color', $project->color) }}"
                    class="h-10 w-16 rounded border border-slate-300 cursor-pointer"
                >
                <span class="text-sm text-slate-500">
                    Usada no calendário para distinguir tarefas por projeto.
                </span>
            </div>
        </div>

        <div class="flex justify-end">
            <button
                type="submit"
                class="px-6 py-2 rounded-2xl bg-atlas-500 text-white text-sm font-semibold hover:bg-atlas-900"
            >
                Guardar alterações
            </button>
        </div>
    </form>
</div>

<div class="rounded-3xl bg-white shadow-sm border border-slate-200 p-6 space-y-4">

    <div>
        <h2 class="text-lg font-semibold text-slate-900">Tags do Projeto</h2>
        <p class="text-sm text-slate-500">
            Organiza tarefas com etiquetas personalizadas.
        </p>
    </div>

    <form id="tagsForm" class="space-y-3">
        <input
            type="hidden"
            name="project_id"
            value="{{ $project->project_id }}"
        >

        <input
            type="text"
            name="tag_name"
            placeholder="Nova tag"
            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
        >

        <button
            type="submit"
            class="w-full rounded-xl bg-atlas-500 py-2 text-sm font-semibold text-white hover:bg-atlas-900"
        >
            Adicionar tag
        </button>
    </form>

    <hr>

    <ul id="existingTags" class="space-y-2 text-sm">
        <li class="text-slate-400">A carregar tags…</li>
    </ul>
</div>

 <div class="lg:col-span-3">
   
                @include('partials.projects.members_card', [
                    'members' => $members,
                    'project' => $project,
                    'userRole' => $userRole
                ])
</div>
    </div>



        </div>
    </main>
</div>
@endsection