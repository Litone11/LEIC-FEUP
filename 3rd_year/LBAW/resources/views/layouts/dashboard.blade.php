<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-id" content="{{ auth()->user()->user_id }}">

        <title>@yield('title', 'Atlas Dashboard')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    
<body class="bg-atlas-50 text-slate-900 antialiased">
    <div id="toast-root" class="pointer-events-none fixed inset-0 z-[99999]">
        @include('partials.flash_toast')
        @include('partials.notification_toast')
    </div>
    <header class="md:hidden sticky top-0 z-30 flex items-center gap-3 bg-white px-4 py-3 border-b border-slate-200">
        <button
            id="openSidebar"
            class="inline-flex items-center rounded-lg p-2 text-slate-600 hover:bg-slate-100"
            aria-label="Open menu"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        
        <span class="text-lg font-semibold text-slate-900 mx-auto">Atlas</span>
        <div class="w-6"></div>
    </header>

    <div class="flex min-h-screen">
        <main class="flex-1">
            @php
                $currentRouteName = request()->route()?->getName();
                $helpTopics = config('help.topics');
                $helpTopic = $currentRouteName && isset($helpTopics[$currentRouteName])
                    ? $helpTopics[$currentRouteName]
                    : null;
            @endphp

            @include('partials.alerts.form_errors')
            @yield('content')
            @includeWhen($helpTopic, 'partials.help.contextual_help', ['topic' => $helpTopic])

        </main>
    </div>

    @stack('scripts')
</body>
</html>
