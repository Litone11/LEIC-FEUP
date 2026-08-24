@extends('layouts.dashboard')

@section('title', "Atlas · {$project->name} Analytics")

@section('content')
<div class="flex min-h-screen">
    @include('partials.sidebar', ['user' => auth()->user()])

    <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12">
        <h1 class="text-3xl font-semibold text-slate-900 mb-6">Distribuição de tarefas - {{ $project->name }}</h1>
    <p class="mb-6 text-slate-600">
    O <strong>Esforço</strong> é calculado somando o peso das tarefas atribuídas e das tarefas pelas quais o utilizador é responsável.Um índice maior indica maior carga de trabalho.
    </p>
        <section class="rounded-3xl border border-slate-200 bg-white overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Membro</th>
                        <th class="px-6 py-3 text-left">Atribuido a</th>
                        <th class="px-6 py-3 text-left">Responsável por</th>
                        <th class="px-6 py-3 text-left">Total de tarefas</th>
                        <th class="px-6 py-3 text-left">Esforço</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workload as $w)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">{{ $w['user'] }}</td>
                            <td class="px-6 py-4">{{ $w['assigned_count'] }}</td>
                            <td class="px-6 py-4">{{ $w['responsible_count'] }}</td>
                            <td class="px-6 py-4">{{ $w['total_tasks'] }}</td>
                                                <td class="px-6 py-4">
                        <span class="
                            @if($w['workload_index'] > 5) text-red-600 font-bold
                            @elseif($w['workload_index'] >= 2.5) text-yellow-600
                            @else text-green-600
                            @endif
                        ">
                            {{ $w['workload_index'] }}
                        </span>
                    </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</div>
@endsection
