@extends('layouts.dashboard')

@section('title', 'Atlas · Notifications')

@section('content')
<div class="flex min-h-screen">

    @include('partials.sidebar', ['user' => $user])

    <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12">

        <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Notificações</h1>
                <p class="text-sm text-slate-500">Consulta convites e avisos da tua conta.</p>
            </div>
            <div class="inline-flex rounded-full border border-slate-200 bg-slate-50 p-1">
                <a href="{{ route('notifications', ['tab' => 'unread']) }}"
                   class="rounded-full px-4 py-2 text-sm font-semibold transition
                   {{ $tab === 'unread' ? 'bg-white text-atlas-600 shadow' : 'text-slate-500 hover:text-slate-800' }}">
                    Por ler ({{ $counts['unread'] ?? 0 }})
                </a>
                <a href="{{ route('notifications', ['tab' => 'read']) }}"
                   class="rounded-full px-4 py-2 text-sm font-semibold transition
                   {{ $tab === 'read' ? 'bg-white text-atlas-600 shadow' : 'text-slate-500 hover:text-slate-800' }}">
                    Lidas ({{ $counts['read'] ?? 0 }})
                </a>
            </div>
        </div>

        @if ($notifications->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                Não tens notificações {{ $tab === 'unread' ? 'por ler' : 'lidas' }}.
            </div>
        @else
           <ul id="notifications-list" class="space-y-3">
                @foreach ($notifications as $notification)
                    @include('partials.notification_card', ['notification' => $notification])
                @endforeach
            </ul>
        @endif

    </main>
</div>
@endsection
