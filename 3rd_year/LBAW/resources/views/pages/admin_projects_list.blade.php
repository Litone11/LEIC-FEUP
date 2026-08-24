@extends('layouts.dashboard')

@section('title', 'Atlas · Admin Projects')

@section('content')
<div class="min-h-screen bg-atlas-50">
    <div class="px-4 sm:px-8 lg:px-12 space-y-6">
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

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-semibold text-slate-900">
                Projetos
            </h1>
        </div>

        {{-- Table --}}
        <section class="rounded-3xl border border-slate-200 bg-white">

            <div class="flex justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="text-xl font-semibold text-slate-900">
                    Todos os projetos
                </h2>

                <label for="admin-projects-filter" class="sr-only">Filtrar projetos</label>
                <input type="text"
                       id="admin-projects-filter"
                       placeholder="Filtrar por nome, descrição ou coordenador"
                       class="w-64 rounded-xl border px-4 py-2 text-sm text-slate-700">
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">Nome</th>
                            <th class="px-6 py-3 text-left">Descrição</th>
                            <th class="px-6 py-3 text-left">Coordenador</th>
                            <th class="px-6 py-3 text-left">Nº de membros</th>
                            <th class="px-6 py-3 text-left">Criado em</th>
                            <th class="px-6 py-3 text-left">Suspenso?</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr class="cursor-pointer hover:bg-slate-50" >
                                <!-- Meter isto = onclick="window.location='{{ route('admin.projects.show', $project['id']) }}'"   -->
                                <td class="px-6 py-4 font-medium">
                                    {{ $project['name'] }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 truncate max-w-sm">
                                    {{ $project['description'] }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $project['coordinator'] }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $project['members'] }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $project['created_at'] }}
                                </td>
                                <td class="px-6 py-4">
                                    //TODO
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="px-6 py-8 text-center text-slate-500">
                                    Nenhum projeto encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</div>
@endsection
