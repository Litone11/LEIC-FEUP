@extends('layouts.dashboard')

@section('title', 'Atlas · Perfil')

@section('content')

@php $isAdmin = $user->isAdmin(); @endphp

@if ($isAdmin)
    <div class="min-h-screen bg-atlas-50 pb-16">
        <div class="px-4 sm:px-8 lg:px-12 space-y-6">
            <div class="sticky top-0 z-20 flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-atlas-50/95 px-4 py-4 backdrop-blur sm:px-0">
                <a href="{{ route('home') }}" class="text-2xl font-semibold text-slate-900">Atlas</a>
                <div class="flex items-center gap-3 text-sm">
                    <a href="{{ route('admin.dashboard') }}"
                       class="rounded-full border border-slate-200 px-4 py-2 font-semibold text-atlas-600 hover:bg-atlas-50">
                        Painel Admin
                    </a>
                    <a href="{{ route('logout') }}"
                       class="rounded-full border border-slate-200 px-4 py-2 font-semibold text-slate-500 hover:bg-slate-100">
                        Terminar sessão
                    </a>
                </div>
            </div>

            @include('partials.profile.profile_header', [
                'user' => $user,
                'stats' => $stats,
                'joinedAt' => $joinedAt,
                'isAdmin' => true,
            ])

            @include('partials.profile.profile_info', [
                'user' => $user,
                'joinedAt' => $joinedAt,
                'isAdmin' => true,
            ])

            {{-- PROFILE MODAL --}}
            @include('partials.modals.profile_modal', [
                'user' => $user,
                'profilePicUrl' => $profilePicUrl,
                'defaultProfilePicUrl' => $defaultProfilePicUrl,
                'canRemoveProfilePic' => $canRemoveProfilePic,
                'showPasswordFields' => $showPasswordFields
            ])
            @include('partials.profile.profile_delete_confirmation')
        </div>
    </div>
@else
    <div class="flex min-h-screen">
        @include('partials.sidebar', ['user' => $user])

        <main class="flex-1 px-4 py-6 sm:px-8 lg:px-12">

            @include('partials.profile.profile_header', [
                'user' => $user,
                'stats' => $stats,
                'joinedAt' => $joinedAt,
                'isAdmin' => false,
            ])

            @include('partials.profile.profile_info', [
                'user' => $user,
                'joinedAt' => $joinedAt,
                'isAdmin' => false,
            ])

            @include('partials.profile.recent_projects', [
                'recentProjects' => $recentProjects
            ])

            {{-- PROFILE MODAL --}}
            @include('partials.modals.profile_modal', [
                'user' => $user,
                'profilePicUrl' => $profilePicUrl,
                'defaultProfilePicUrl' => $defaultProfilePicUrl,
                'canRemoveProfilePic' => $canRemoveProfilePic,
                'showPasswordFields' => $showPasswordFields
            ])
            @include('partials.profile.profile_delete_confirmation')

        </main>
    </div>
@endif

@endsection
