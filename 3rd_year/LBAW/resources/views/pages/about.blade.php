@extends('layouts.marketing')

@section('title', 'Atlas | Sobre nós')

@section('content')
    <header class="border-b border-slate-200 bg-gradient-to-r from-indigo-50 via-white to-rose-50">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="text-2xl font-semibold tracking-tight text-slate-900">Atlas</a>
            <div class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
                <a href="{{ route('about') }}" class="text-slate-900">Sobre Nós</a>
                <a href="{{ route('features') }}" class="hover:text-slate-900">Funcionalidades</a>
                <a href="{{ route('contact') }}" class="hover:text-slate-900">Contactos</a>
            </div>
            <div class="flex items-center gap-3 text-sm font-semibold">
                @auth
                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="rounded-full border border-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-100">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-full border border-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-100 md:inline-flex">Login</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-slate-900 px-4 py-2 text-white transition hover:bg-slate-800">Sign up</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-12 space-y-10">
        <section class="rounded-3xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-rose-50 p-8 shadow-sm">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-indigo-700">
                <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                Sobre nós
            </p>
            <h1 class="mt-4 text-3xl font-semibold text-slate-900 sm:text-4xl">Mapeie os seus projetos.</h1>
            <p class="mt-4 text-base text-slate-700">
                O Atlas nasceu de uma equipa que precisava de planear, executar e comunicar progresso os seus projetos.
            </p>
        </section>

        <section class="grid gap-6 sm:grid-cols-2">
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Missão</p>
                <h3 class="mt-2 text-lg font-semibold text-slate-900">Transparência para toda a equipa</h3>
                <p class="mt-2 text-sm text-slate-700">Criar um espaço onde objetivos, tarefas e riscos são visíveis e acionáveis.</p>
            </div>
            <div class="rounded-2xl border border-rose-100 bg-rose-50 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Valores</p>
                <h3 class="mt-2 text-lg font-semibold text-slate-900">Foco, simplicidade, confiança</h3>
                <p class="mt-2 text-sm text-slate-700">Criámos um espaço que facilita a organização de ideias, a colaboração no trabalho e a comunicação eficiente entre equipas.</p>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Como usar o Atlas</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-700">
                <li>• Criar o projeto </li>
                <li>• Adicionar os membros da equipa</li>
                <li>• Criar e atribuir tarefas</li>
                <li>• Ver a magia acontecer</li>
            </ul>
        </section>

        @guest
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Queres experimentar?</h3>
                        <p class="text-sm text-slate-600">Cria conta ou faz login para ver o dashboard.</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('register') }}" class="rounded-2xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">Criar conta</a>
                        <a href="{{ route('login') }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-slate-800 hover:bg-slate-50">Entrar</a>
                    </div>
                </div>
            </section>
        @endguest
    </main>
    @include('partials.marketing.footer')
@endsection
