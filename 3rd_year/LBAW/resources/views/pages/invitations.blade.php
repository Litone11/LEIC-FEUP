@extends('layouts.dashboard')

@section('title', 'Atlas · Invitations')

@section('content')
<div class="flex min-h-screen">

    @include('partials.sidebar', ['user' => $user])

    <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12">

        <div class="mb-10">
            <p class="text-sm text-slate-500">Todas as convites</p>
            <h1 class="text-3xl font-semibold text-slate-900">Convites</h1>
        </div>

        @if ($invitations->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                 Sem convites pendentes.
            </div>
        @else
           <ul id="invitations-list" class="space-y-3">
                @foreach ($invitations as $invitation)
                    <li class="rounded-xl border border-slate-200 bg-white p-6 flex justify-between items-center">
                        <div>
                            <p class="text-sm text-slate-500">Projeto:</p>
                            <h2 class="text-lg font-semibold text-slate-900">{{ $invitation->project->name }}</h2>
                        </div>
                        <div class="flex gap-3">
                            <form action="{{ route('invitations.accept', $invitation) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-atlas-500 text-white rounded-lg hover:bg-atlas-900">
                                    Aceitar
                                </button>
                            </form>
                                <form action="{{ route('invitations.decline', $invitation) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                        Recusar
                                    </button>
                                </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

    </main>
</div>
@endsection
