{{-- resources/views/partials/sidebar.blade.php --}}

@php
    $isProjectPage = request()->routeIs('projects.show')
        || request()->routeIs('tasks.index')
        || request()->routeIs('tasks.show')
        || request()->routeIs('projects.members')
        || request()->routeIs('projects.settings')
        || request()->routeIs('projects.forum')
        || request()->routeIs('projects.forum.topic');

    // Get current project id safely
    $currentProjectId = $project->project_id
        ?? request()->route('project')
        ?? null;

    $sidebarAvatar = method_exists($user, 'profilePictureUrl')
        ? $user->profilePictureUrl()
        : asset('images/default-profile.svg');
@endphp
<div
  id="sidebarOverlay"
  class="fixed inset-0 z-40 hidden bg-black/40 md:hidden
         opacity-0 transition-opacity duration-300"
></div>
<aside
  id="sidebar"
  class="fixed inset-y-0 left-0 z-50 w-64 bg-white transform
         -translate-x-full transition-transform duration-300
         flex flex-col border-r border-slate-200 px-6 py-8
         md:static md:translate-x-0 md:sticky md:top-0 md:h-screen md:overflow-y-auto"
>

    {{-- Logo --}}
    <div class="text-3xl font-semibold text-slate-900">
        <a href="{{ route('home') }}">Atlas</a>
    </div>

    <div class="mt-4 flex-1 flex flex-col">
        <nav class="flex flex-1 flex-col justify-center gap-1 text-sm font-medium text-slate-500">

            {{-- ============================
                PROJECT CONTEXT
            ============================= --}}
            @if ($isProjectPage && $currentProjectId)

                {{-- Overview --}}
                <a href="{{ route('projects.show', $currentProjectId) }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100
                          {{ request()->routeIs('projects.show') ? 'bg-atlas-50 text-slate-900' : '' }}">
                    @if (request()->routeIs('projects.show'))
                        <span class="h-2 w-2 rounded-full bg-atlas-500"></span>
                    @endif
                    Project Overview
                </a>

                {{-- Tasks --}}
                <a href="{{ route('tasks.index', ['project' => $currentProjectId]) }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100
                          {{ request()->routeIs('tasks.index') ? 'bg-atlas-50 text-slate-900' : '' }}">
                    @if (request()->routeIs('tasks.index'))
                        <span class="h-2 w-2 rounded-full bg-atlas-500"></span>
                    @endif
                    Tarefas
                </a>

                {{-- Team / Equipa --}}
                <a href="{{ route('projects.members', $currentProjectId) }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100
                          {{ request()->routeIs('projects.members') ? 'bg-atlas-50 text-slate-900' : '' }}">
                    @if (request()->routeIs('projects.members'))
                        <span class="h-2 w-2 rounded-full bg-atlas-500"></span>
                    @endif
                    Equipa
                </a>

                {{-- Analytics --}}
                <a href="{{route('projects.analytics',$currentProjectId)}}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100">
                    Analytics
                </a>

                {{-- Forum --}}
                <a href="{{ route('projects.forum', $currentProjectId) }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100
                          {{ request()->routeIs('projects.forum') || request()->routeIs('projects.forum.topic') ? 'bg-atlas-50 text-slate-900' : '' }}">
                    @if (request()->routeIs('projects.forum') || request()->routeIs('projects.forum.topic'))
                        <span class="h-2 w-2 rounded-full bg-atlas-500"></span>
                    @endif
                    Fórum
                </a>

                {{-- Back to Projects --}}
                <a href="{{ route('projects') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100">
                    <i class="bi bi-arrow-left text-lg"></i>
                    Voltar aos Projetos
                </a>

            {{-- ============================
                GENERAL CONTEXT
            ============================= --}}
            @else

                {{-- Dashboard --}}
                <a href="{{ route('dashboard')}}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100
                          {{ request()->routeIs('dashboard') ? 'bg-atlas-50 text-slate-900' : '' }}">
                    @if (request()->routeIs('dashboard'))
                        <span class="h-2 w-2 rounded-full bg-atlas-500"></span>
                    @endif
                    Dashboard
                </a>

                {{-- Projects --}}
                <a href="{{ route('projects') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100
                          {{ request()->routeIs('projects') || request()->routeIs('projects.create') ? 'bg-atlas-50 text-slate-900' : '' }}">
                    @if (request()->routeIs('projects') || request()->routeIs('projects.create'))
                        <span class="h-2 w-2 rounded-full bg-atlas-500"></span>
                    @endif
                    Projetos
                </a>

                {{-- Calendar --}}
                <a href="{{ route('calendar') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100
                        {{ request()->routeIs('calendar') ? 'bg-atlas-50 text-slate-900' : '' }}">
                    @if (request()->routeIs('calendar'))
                        <span class="h-2 w-2 rounded-full bg-atlas-500"></span>
                    @endif
                    Calendário
                </a>
              {{-- Notifications --}}
                <a href="{{ route('notifications') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100
                        {{ request()->routeIs('notifications') ? 'bg-atlas-50 text-slate-900' : '' }}">
                    @if (request()->routeIs('notifications'))
                        <span class="h-2 w-2 rounded-full bg-atlas-500"></span>
                    @endif
                    Notificações
                </a>

            @endif

            @if (! $isProjectPage && $user->isAdmin())
                <div class="mt-4 border-t border-slate-100 pt-4">
                    <p class="px-4 pb-2 text-xs font-semibold uppercase text-slate-400">Área administrativa</p>

                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100
                              {{ request()->routeIs('admin.dashboard') ? 'bg-atlas-50 text-slate-900' : '' }}">
                        Painel Admin
                    </a>

                    <a href="{{ route('admin.projects.index') }}"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 hover:bg-slate-100
                              {{ request()->routeIs('admin.projects.*') ? 'bg-atlas-50 text-slate-900' : '' }}">
                        Projetos (Admin)
                    </a>
                </div>
            @endif

        </nav>
    </div>

    {{-- Sidebar Footer --}}
    <div class="mt-4 rounded-2xl bg-slate-100 p-4 text-sm text-slate-600 flex flex-col gap-3">
    
        <div class="flex items-center gap-3">
            <img
                src="{{ $sidebarAvatar }}"
                alt="Foto de perfil de {{ $user->username }}"
                class="h-12 w-12 rounded-2xl object-cover ring-2 ring-white shadow-sm"
                loading="lazy"
            >
            <div class="min-w-0">
                <p class="font-semibold text-slate-900 truncate">{{ $user->username }}</p>
                <a href="{{ route('profile') }}" class="inline-flex items-center text-xs font-semibold text-atlas-500 hover:text-atlas-900">
                    Perfil &amp; Definições
                </a>
            </div>
        </div>

        <a href="{{ route('logout') }}"
           class="inline-flex items-center text-xs font-semibold text-atlas-500 hover:text-atlas-900">
            Terminar sessão
        </a>

        <div class="mt-4 border-t border-slate-300 pt-3 space-y-1.5 text-xs">
            <p class="font-semibold text-slate-700">Sobre o Atlas</p>
            <a href="{{ route('home') }}" class="block text-slate-500 hover:text-slate-900 transition">
                ➤ Homepage
            </a>
            <a href="{{ route('about') }}" class="block text-slate-500 hover:text-slate-900 transition">
                ➤ Sobre nós
            </a>
            <a href="{{ route('features') }}" class="block text-slate-500 hover:text-slate-900 transition">
                ➤ Funcionalidades
            </a>
            <a href="{{ route('contact') }}" class="block text-slate-500 hover:text-slate-900 transition">
                ➤ Contactos
            </a>
        </div>
    </div>

</aside>
