@extends('layouts.dashboard')

@section('title', 'Atlas · Fórum · ' . $project->name)

@section('content')
<div class="flex min-h-screen">
    @include('partials.sidebar', ['user' => $user, 'project' => $project])

    <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12 space-y-8">

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('projects.forum', $project) }}"
                   class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-atlas-500">
                    <span>&larr;</span> Voltar ao fórum
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
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm space-y-6">
            <header class="space-y-2">
                <p class="text-sm text-slate-500">
                    Aberto por {{ $topic->author?->username ?? 'Utilizador removido' }} ·
                    {{ optional($topic->created_at)->format('d M Y') }}
                </p>
                <h2 class="text-2xl font-semibold text-slate-900">{{ $topic->title }}</h2>
                @if ($topic->task)
                    <a href="{{ route('tasks.show', $topic->task->task_id) }}"
                       class="inline-flex items-center gap-2 rounded-full bg-atlas-50 px-3 py-1 text-xs font-semibold text-atlas-700">
                        <i class="bi bi-link-45deg"></i> Ver tarefa: {{ $topic->task->name }}
                    </a>
                @endif
            </header>

            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-slate-800">
                {!! nl2br(e($topic->body)) !!}
            </article>

            <div class="flex items-center gap-4">
                <form method="POST" action="{{ route('projects.forum.like', [$project, $topic]) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold {{ $liked ? 'border-atlas-200 bg-atlas-50 text-atlas-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                        <i class="bi bi-hand-thumbs-up{{ $liked ? '-fill' : '' }}"></i>
                        {{ $liked ? 'Remover gosto' : 'Gostar' }}
                    </button>
                </form>
                <span class="text-sm text-slate-600 inline-flex items-center gap-1">
                    <i class="bi bi-hand-thumbs-up"></i>
                    {{ $topic->likes->count() }} gostos
                </span>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-slate-900">Respostas</h3>

                @forelse ($topic->replies as $reply)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    {{ $reply->author?->username ?? 'Utilizador removido' }}
                                </p>
                                <p class="text-xs text-slate-500">{{ optional($reply->created_at)->format('d M Y') }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-slate-700 whitespace-pre-line">{{ $reply->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Ainda não existem respostas.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('projects.forum.reply', [$project, $topic]) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-6">
                @csrf
                <label class="text-sm font-semibold text-slate-700">Adicionar resposta</label>
                <textarea name="body" rows="4" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>{{ old('body') }}</textarea>
                <div class="flex justify-end">
                    <button type="submit"
                            class="rounded-2xl bg-atlas-500 px-4 py-2 text-sm font-semibold text-white hover:bg-atlas-600">
                        Responder
                    </button>
                </div>
            </form>
        </section>

    </main>
</div>
@endsection
