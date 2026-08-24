@extends('layouts.auth')

@section('title', 'Nova Password')

@section('content')
    <div class="mb-8 space-y-3 text-center sm:text-left">
<!--         <p class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-indigo-700">
            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
            Entrar
        </p> -->
        <h1 class="text-3xl font-semibold text-slate-900"> Nova Password</h1>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-6 rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">


        <div>
            <label for="email" class="text-sm font-semibold text-slate-900">E-mail</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ $email ?? old('email') }}"
                required
                autofocus
                inputmode="email"
                autocomplete="email"
                @class([
                    'mt-2 w-full rounded-2xl border bg-white px-4 py-3 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-200',
                    'border-rose-400 focus:ring-rose-200' => $errors->has('email'),
                    'border-slate-200 focus:ring-indigo-200' => ! $errors->has('email'),
                ])
                placeholder="exemplo@gmail.com"
            >
            @error('email')
                <p id="email-error" class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
            @enderror
        </div>
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
            Trocar Password
    </button>
    
    <p class="mt-6 text-center text-sm text-slate-600">
            Ainda não tens acesso? <a href="{{ route('register') }}" class="font-semibold text-indigo-700 hover:text-indigo-900">Cria uma conta gratuita</a>
    </p>
        </div>



      

             
        @if (session('status'))
            <p class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ session('status') }}
            </p>
        @endif
    </form>



@endsection
