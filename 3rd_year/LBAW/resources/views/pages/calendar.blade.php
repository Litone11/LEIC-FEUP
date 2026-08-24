@extends('layouts.dashboard')

@section('title', 'Atlas · Calendar')

@section('content')
<div class="flex h-screen overflow-hidden">

    @include('partials.sidebar', ['user' => $user])

    <main class="flex-1 flex flex-col min-h-0 px-4 py-6 sm:px-8 lg:px-12">
        <div class="mb-4">
            <p class="text-sm text-slate-500">View your tasks by project</p>
            <h1 class="text-3xl font-semibold text-slate-900">Calendar</h1>
        </div>

     <section class="mb-4 rounded-2xl border border-slate-200 bg-white p-4">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-slate-800">
            Filter projects
        </h2>

        @if ($projects->count() > 3)
            <div class="flex gap-2">
                <button
                    id="projects-prev"
                    class="rounded-lg border border-slate-200 px-3 py-1 text-sm hover:bg-slate-50"
                >
                    ◀
                </button>
                <button
                    id="projects-next"
                    class="rounded-lg border border-slate-200 px-3 py-1 text-sm hover:bg-slate-50"
                >
                    ▶
                </button>
            </div>
        @endif
    </div>

    @if ($projects->isEmpty())
        <p class="text-sm text-slate-500">
            You are not a member of any projects.
        </p>
    @else
        <div
            id="projects-carousel"
            class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3"
        >
            @foreach ($projects as $project)
                <label
                    class="project-item hidden flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 transition hover:bg-slate-50"
                >
                    <input
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-atlas-500 focus:ring-atlas-500"
                        data-project-id="{{ $project->project_id }}"
                    >

                    <span class="text-sm font-medium text-slate-700">
                        {{ $project->name }}
                    </span>
                </label>
            @endforeach
        </div>
    @endif
</section>

        <section class="flex-1 min-h-0 rounded-2xl border border-slate-200 bg-white p-3">
            <div id="calendar" class="h-full"></div>
        </section>
    </main>
</div>
@endsection
