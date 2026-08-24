@extends('layouts.dashboard')

@section('title', 'Atlas · Fórum · ' . $project->name)

@section('content')
@php
    use Illuminate\Support\Str;
@endphp
<div class="flex min-h-screen">
    @include('partials.sidebar', ['user' => $user, 'project' => $project])
    @include('partials.modals.forum_new_topic_modal', ['project' => $project])

    <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12 space-y-8">

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('projects.show', $project) }}"
                   class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-atlas-500">
                    <span>&larr;</span> Voltar ao projeto
                </a>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    Fórum do projeto
                </span>
            </div>

            <div>
                <p class="text-sm text-slate-500">Projeto</p>
                <h1 class="text-3xl font-semibold text-slate-900">{{ $project->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $project->description }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase text-slate-500">Progresso</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['progress'] }}%</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase text-slate-500">Tarefas</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900">{{ $summary['tasks_ratio'] }}</p>
                    <p class="text-xs text-slate-500">Concluídas / Total</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase text-slate-500">Equipa</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $summary['team_count'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase text-slate-500">Início</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900">{{ $summary['start_date'] ?? 'Sem data' }}</p>
                </div>
            </div>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm space-y-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-slate-500">Discussões</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Fórum do projeto</h2>
                </div>
                <button type="button"
                        data-open-modal="forum-topic-modal"
                        class="inline-flex items-center gap-2 rounded-2xl bg-atlas-500 px-4 py-2 text-sm font-semibold text-white hover:bg-atlas-600">
                    <i class="bi bi-plus-lg"></i> Novo tópico
                </button>
            </div>

            <!-- @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif -->

            <div class="space-y-3">
                @forelse ($topics as $topic)
                    <a href="{{ route('projects.forum.topic', [$project, $topic]) }}"
                       class="block rounded-2xl border border-slate-200 bg-white px-5 py-4 hover:border-atlas-200 hover:bg-atlas-50">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <p class="text-sm text-slate-500">Aberto por {{ $topic->author?->username ?? 'Utilizador removido' }}</p>
                                <h3 class="text-lg font-semibold text-slate-900">{{ $topic->title }}</h3>
                                @if ($topic->task)
                                    <p class="text-xs inline-flex items-center gap-1 rounded-full bg-atlas-50 px-2 py-1 font-semibold text-atlas-700">
                                        <i class="bi bi-link-45deg"></i> Tarefa: {{ $topic->task->name }}
                                    </p>
                                @endif
                                <p class="text-sm text-slate-600 line-clamp-2">{{ Str::limit($topic->body, 160) }}</p>
                            </div>
                            <div class="text-right text-xs text-slate-500">
                                <p>{{ optional($topic->created_at)->format('d M Y') }}</p>
                                <p class="mt-1 font-semibold text-slate-700">{{ $topic->replies_count }} respostas</p>
                                <p class="mt-1 inline-flex items-center gap-1 text-atlas-600 font-semibold">
                                    <i class="bi bi-hand-thumbs-up"></i> {{ $topic->likes_count ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                        <p class="text-lg font-semibold text-slate-800">Ainda não existem tópicos.</p>
                        <p class="mt-2 text-sm text-slate-500">Cria o primeiro tópico para iniciar a discussão.</p>
                    </div>
                @endforelse
            </div>
        </section>

    </main>
</div>
@endsection
