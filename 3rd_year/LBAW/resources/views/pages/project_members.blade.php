@extends('layouts.dashboard')

@section('title', 'Atlas · Membros · ' . $project->name)

@section('content')

@php
    $isCoordinator = ($userRole === 'coordinator');
@endphp

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    @include('partials.sidebar', ['user' => $user, 'project' => $project])

    {{-- Main Content --}}
    <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12 space-y-8">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Membros do projeto: {{ $project->name }}
            </h1>
            <p class="text-slate-500">
                {{ $summary['team_count'] }} pessoas na equipa
            </p>
        </div>

    {{-- Members List Card --}}
    <div id="membersCard"
        class="rounded-3xl bg-white shadow-sm border border-slate-200 p-6 space-y-6">

        {{-- Header row --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between md:gap-4">

            {{-- Title --}}
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Lista de membros</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Gere e consulta todos os membros deste projeto.
                </p>
            </div>

            {{-- Search --}}
            <form class="w-full md:max-w-md" method="GET" action="{{ route('projects.members', $project) }}">
                <label for="membersSearch" class="sr-only">Pesquisar membros</label>
                <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-500">
                    <i class="bi bi-search text-slate-400"></i>
                    <input type="text"
                        id="membersSearch"
                        name="search"
                        value="{{ $search ?? '' }}"
                        placeholder="Procurar membros por nome ou email"
                        class="w-full border-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                    <button type="submit" class="rounded-xl border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">Pesquisar</button>
                </div>
            </form>
            <button
                type="button"
                id="open-user-profile-trigger"
                data-open-user-profile
                class="hidden">
            </button>
            {{-- Buttons --}}
            @if ($isCoordinator)
                <div class="flex items-center gap-2">
                   <button
                        id="addMemberBtn"
                        data-open-modal="add-member-modal"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-purple-700 bg-purple-100 border border-purple-300 px-3 py-2 rounded-full hover:bg-purple-200 hidden">
                        <i class="bi bi-plus-lg text-purple-700"></i>
                        Adicionar membros
                    </button>
                    <button
                        type="button"
                        id="inviteByEmailBtn"
                        data-open-modal="invite-member-modal"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-100 border border-green-300 px-3 py-2 rounded-full hover:bg-green-200 hidden">
                        <i class="bi bi-envelope-plus text-green-700"></i>
                        Convidar por email
                    </button>

                    {{-- Toggle management mode --}}
                    <button id="manageMembersBtn"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-100 border border-amber-300 px-3 py-2 rounded-full hover:bg-amber-200">
                        <i class="bi bi-gear-fill text-amber-600"></i>
                        Gerir membros
                    </button>
                </div>
            @endif

        </div>

        {{-- Members list --}}
        <ul id="membersList" class="space-y-3">
            @foreach ($members as $member)
                <li class="member-row flex items-center justify-between border border-slate-100 rounded-xl px-4 py-2"
                    data-member-id="{{ $member->user_id }}"
                    data-member-name="{{ strtolower($member->username) }}"
                    data-member-email="{{ strtolower($member->email) }}">


                    <div class="flex items-center gap-3">
                        <img src="{{ $member->profilePictureUrl() }}"
                            class="h-10 w-10 rounded-full object-cover"
                            alt="Foto de {{ $member->username }}">
                        <div>
                            <p class="font-medium text-slate-900">{{ $member->username }}</p>
                            <p class="text-xs text-slate-500">{{ $member->email }}</p>
                        </div>
                    <div>
                    {{-- Normal view badge --}}
                    <span class="role-badge
                        text-xs font-semibold px-2 py-1 rounded-full uppercase
                        @if ($member->pivot->user_role === 'coordinator')
                            bg-purple-100 text-purple-700
                        @else
                            bg-slate-100 text-slate-600
                        @endif">
                        {{ $member->pivot->user_role }}
                    </span>
                     <div>

                    <button
                        class="promoteMemberBtn hidden text-xs font-semibold text-atlas-500 hover:text-purple-700 px-4"
                        data-member-id="{{ $member->user_id }}"
                        data-member-name="{{ $member->username }}"
                        data-open-modal= "make-coordinator-modal"
                        
                    >
                        Tornar coordenador
                    </button>

                    {{-- Hidden remove button --}}
                    
                    <button 
                        class="removeMemberBtn hidden text-xs font-semibold text-red-600 hover:text-red-800"
                        data-member-id="{{ $member->user_id }}"
                        data-member-name="{{ $member->username }}"
                        data-open-modal="remove-member-modal"
                    >
                        Remover do projeto
                    </button>
                </div>
                </div>
                </li>
            @endforeach
        </ul>
        <p id="membersSearchEmpty"
           class="hidden text-center text-sm text-slate-500">
            Nenhum membro corresponde à pesquisa.
        </p>

    </div>


    </main>
</div>

@include('partials.modals.delete_member_modal')
@include('partials.modals.add_members_modal')
@include('partials.modals.make_coordinator_modal')
@include('partials.modals.member_profile_modal')
@include('partials.modals.invite_email_modal')

@endsection
