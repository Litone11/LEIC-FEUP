<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Atlas - Área segura')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="bg-gradient-to-br from-indigo-50 via-white to-rose-50 text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col md:flex-row">
            <aside class="relative hidden flex-1 overflow-hidden bg-gradient-to-br from-indigo-100 via-white to-rose-100 p-12 md:flex">
                <div class="pointer-events-none absolute -left-10 top-10 h-72 w-72 rounded-full bg-indigo-200/60 blur-[120px]"></div>
                <div class="pointer-events-none absolute bottom-10 right-6 h-64 w-64 rounded-full bg-rose-200/60 blur-[120px]"></div>
                <div class="relative z-10 flex flex-col justify-between">
                    <div>
                        <a href="{{ route('home') }}" class="text-3xl font-semibold text-slate-900">Atlas</a>
                        <p class="mt-6 max-w-sm text-lg text-slate-700">
                            Sincroniza equipas híbridas, acompanha progresso e comunica resultados com clareza. Tudo começa aqui.
                        </p>
                    </div>
                    <div class="mt-16 space-y-6 rounded-3xl border border-indigo-100 bg-white/80 p-6 text-sm text-slate-700 shadow-sm backdrop-blur">
                        @hasSection('aside')
                            @yield('aside')
                        @else
                            <p class="text-base font-medium text-slate-900">Confiado por equipas críticas</p>
                            <p>Os quadros Atlas conectam objetivos e execução com insights em tempo real.</p>
                            <ul class="mt-4 space-y-2 text-slate-700">
                                <li>• Automatiza rotinas diárias</li>
                                <li>• Partilha contexto entre áreas</li>
                                <li>• Mantém decisões documentadas</li>
                            </ul>
                        @endif
                    </div>
                </div>
            </aside>

            <main class="flex flex-1 items-center justify-center px-6 py-12 sm:px-10">
                <div class="w-full max-w-md">
                    @include('partials.alerts.form_errors')
                    @yield('content')
                </div>
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
