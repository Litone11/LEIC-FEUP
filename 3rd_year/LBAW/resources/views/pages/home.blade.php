@extends('layouts.marketing')

@section('title', 'Atlas | Gestão de projetos simplificada')

@section('content')
    <header class="border-b border-slate-200 bg-gradient-to-r from-indigo-50 via-white to-rose-50">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="text-2xl font-semibold tracking-tight text-slate-900">Atlas</a>
            <div class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
                <a href="{{ route('about') }}" class="hover:text-slate-900">About</a>
                <a href="{{ route('features') }}" class="hover:text-slate-900">Main Features</a>
                <a href="{{ route('contact') }}" class="hover:text-slate-900">Contact</a>
            </div>
            <div class="flex items-center gap-3 text-sm font-semibold">
                @auth
                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                       class="rounded-full border border-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-100">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-full border border-slate-200 px-4 py-2 text-slate-700 transition hover:bg-slate-100 md:inline-flex">Login</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-slate-900 px-4 py-2 text-white transition hover:bg-slate-800">Sign up</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-12 space-y-16">
    <section class="grid gap-8 rounded-3xl bg-gradient-to-br from-indigo-50 via-white to-rose-50 p-8 shadow-sm lg:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-4">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/60 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-indigo-700">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                    Antes do login
                </p>
                <h1 class="text-4xl font-semibold text-slate-900 sm:text-5xl">Uma visão clara do que é o Atlas.</h1>
                <p class="text-lg text-slate-600">
                    Explora quem somos, o que oferecemos e como falar connosco. Cada página é simples e direta, para que saibas se o Atlas é o que procuras.
                </p>
                <div class="flex flex-col gap-3 text-sm font-semibold sm:flex-row sm:items-center">
                    <a href="{{ route('about') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-white hover:bg-slate-800">
                        Sobre nós
                    </a>
                    <a href="{{ route('features') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-slate-800 hover:bg-slate-50">
                        Ver funcionalidades
                    </a>
                </div>
            </div>
            <div class="grid gap-4">
                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5">
                    <p class="text-sm font-semibold text-indigo-700">Resumo</p>
                    <p class="mt-2 text-base text-slate-800">Atlas ajuda equipas a organizar trabalho, cumprir prazos e manter as pessoas alinhadas.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_10px_30px_-20px_rgba(99,102,241,0.6)]">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">About</p>
                        <p class="mt-2 text-sm text-slate-700">Quem somos, a nossa missão e como trabalhamos.</p>
                        <a href="{{ route('about') }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-700 hover:underline">Ler mais</a>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_10px_30px_-20px_rgba(236,72,153,0.35)]">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Main Features</p>
                        <p class="mt-2 text-sm text-slate-700">O essencial do produto: planeamento, tarefas e acompanhamento.</p>
                        <a href="{{ route('features') }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-700 hover:underline">Ver detalhes</a>
                    </div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4 sm:col-span-2 shadow-[0_12px_40px_-24px_rgba(236,72,153,0.65)]">
                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Contact</p>
                        <p class="mt-2 text-sm text-slate-700">Precisas de falar connosco? Encontra contactos diretos e horários.</p>
                        <a href="{{ route('contact') }}" class="mt-3 inline-flex text-sm font-semibold text-rose-700 hover:underline">Contactar</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 sm:grid-cols-3">
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">About</p>
                <h3 class="mt-3 text-lg font-semibold text-slate-900">Conhece a equipa</h3>
                <p class="mt-2 text-sm text-slate-700">História, valores e a forma como apoiamos equipas que planeiam e entregam projetos.</p>
                <a href="{{ route('about') }}" class="mt-4 inline-flex text-sm font-semibold text-indigo-700 hover:underline">Abrir página</a>
            </div>
            <div class="rounded-2xl border border-rose-100 bg-rose-50 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Main Features</p>
                <h3 class="mt-3 text-lg font-semibold text-slate-900">O que o produto faz</h3>
                <p class="mt-2 text-sm text-slate-700">Principais módulos, fluxos de trabalho e vantagens para o dia a dia.</p>
                <a href="{{ route('features') }}" class="mt-4 inline-flex text-sm font-semibold text-rose-700 hover:underline">Abrir página</a>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Contact</p>
                <h3 class="mt-3 text-lg font-semibold text-slate-900">Fala connosco</h3>
                <p class="mt-2 text-sm text-slate-700">Email, telefone e horário para esclarecer dúvidas antes de aderires.</p>
                <a href="{{ route('contact') }}" class="mt-4 inline-flex text-sm font-semibold text-slate-900 hover:underline">Abrir página</a>
            </div>
        </section>

        @guest
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Pronto para testar?</p>
                        <h3 class="text-2xl font-semibold text-slate-900">Cria conta e vê o dashboard em ação.</h3>
                        <p class="mt-2 text-sm text-slate-600">Se já tens credenciais, faz login e continua de onde paraste.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-white hover:bg-slate-800">Criar conta</a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-slate-800 hover:bg-slate-50">Entrar</a>
                    </div>
                </div>
            </section>
        @endguest
    </main>
    @include('partials.marketing.footer')
@endsection
