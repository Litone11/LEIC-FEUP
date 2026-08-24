@extends('layouts.auth')

@section('title', 'Recuperar Password')

@section('content')
    <div class="mb-8 space-y-3 text-center sm:text-left">
        <p class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-indigo-700">
            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
            Entrar
        </p>
        <h1 class="text-3xl font-semibold text-slate-900">Recuperar Conta</h1>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6 rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm">
        @csrf

        <div>
            <label for="email" class="text-sm font-semibold text-slate-900">E-mail</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                inputmode="email"
                autocomplete="email"
                @class([
                    'mt-2 w-full rounded-2xl border bg-white px-4 py-3 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-200',
                    'border-rose-400 focus:ring-rose-200' => $errors->has('email'),
                    'border-slate-200 focus:ring-indigo-200' => ! $errors->has('email'),
                ])
                placeholder="nome@empresa.com"
            >
            @error('email')
                <p id="email-error" class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

      

        <button type="submit" class="w-full rounded-full bg-gradient-to-r from-indigo-600 to-rose-500 px-6 py-3 text-base font-semibold text-white transition hover:from-indigo-500 hover:to-rose-400">
            Enviar Email
    </button>
             
        @if (session('status'))
            <p class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ session('status') }}
            </p>
        @endif
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Ainda não tens acesso? <a href="{{ route('register') }}" class="font-semibold text-indigo-700 hover:text-indigo-900">Cria uma conta gratuita</a>
    </p>

@endsection
