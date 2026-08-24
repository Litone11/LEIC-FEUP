@extends('layouts.auth')

@section('title', 'Criar conta no Atlas')

@section('content')
    <div class="mb-8 space-y-3 text-center sm:text-left">
        <p class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-indigo-700">
            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
            Registar
        </p>
        <h1 class="text-3xl font-semibold text-slate-900">Cria a tua conta Atlas</h1>
        <p class="text-sm text-slate-600">Sem cartão de crédito. Começa a colaborar agora.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6 rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm">
        @csrf

        <div>
            <label for="username" class="text-sm font-semibold text-slate-900">Nome</label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username') }}"
                required
                autofocus
                autocomplete="name"
                @class([
                    'mt-2 w-full rounded-2xl border bg-white px-4 py-3 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-200',
                    'border-rose-400 focus:ring-rose-200' => $errors->has('username'),
                    'border-slate-200 focus:ring-indigo-200' => ! $errors->has('username'),
                ])
                placeholder="Joana Costa"
            >
            @error('username')
                <p id="username-error" class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="text-sm font-semibold text-slate-900">E-mail profissional</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                inputmode="email"
                @class([
                    'mt-2 w-full rounded-2xl border bg-white px-4 py-3 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-200',
                    'border-rose-400 focus:ring-rose-200' => $errors->has('email'),
                    'border-slate-200 focus:ring-indigo-200' => ! $errors->has('email'),
                ])
                placeholder="equipa@empresa.com"
            >
            @error('email')
                <p id="email-error" class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="text-sm font-semibold text-slate-900">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                @class([
                    'mt-2 w-full rounded-2xl border bg-white px-4 py-3 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-200',
                    'border-rose-400 focus:ring-rose-200' => $errors->has('password'),
                    'border-slate-200 focus:ring-indigo-200' => ! $errors->has('password'),
                ])
                placeholder="••••••••"
            >
            @error('password')
                <p id="password-error" class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password-confirm" class="text-sm font-semibold text-slate-900">Confirmar password</label>
            <input
                id="password-confirm"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                placeholder="Repete a password"
            >
        </div>

        <button type="submit" class="w-full rounded-full bg-gradient-to-r from-indigo-600 to-rose-500 px-6 py-3 text-base font-semibold text-white transition hover:from-indigo-500 hover:to-rose-400">
            Criar conta
        </button>
          <a href="{{ route('google-auth') }}" 
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50">
            <span>Criar com o Google</span>
        </a><!--Meter a logo-->
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Já tens conta? <a href="{{ route('login') }}" class="font-semibold text-indigo-700 hover:text-indigo-900">Inicia sessão</a>
    </p>
@endsection
