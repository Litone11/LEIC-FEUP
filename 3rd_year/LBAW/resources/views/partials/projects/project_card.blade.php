@props(['project'])

@php
    $isFavorite    = $project['is_favorite'] ?? false;
    $isCoordinator = $project['is_coordinator'] ?? false;
    $isArchived    = $project['is_archived'] ?? false;
    $archivedView  = $archivedView ?? false;
@endphp

<div class="relative min-w-[340px] rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">

    {{-- CLICKABLE CARD --}}
    <a href="{{ route('projects.show', $project['id']) }}"
       class="block p-6 rounded-3xl">

        @if ($isCoordinator)
            <span class="absolute right-4 top-4 inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-[10px] font-semibold text-amber-700 border border-amber-300">
                <i class="bi bi-award-fill text-amber-600"></i> Cord.
            </span>
        @endif

        <div class="space-y-4">

            <p class="text-lg font-semibold text-slate-900">
                {{ $project['name'] }}
            </p>

            <p class="text-sm text-slate-500">
                {{ $project['description'] }}
            </p>

            <div>
                <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <span>Progresso</span>
                    <span>{{ $project['progress'] }}%</span>
                </div>

                <div class="mt-2 h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-atlas-500" style="width: {{ $project['progress'] }}%"></div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1">
                        <i class="bi bi-calendar3"></i>
                        {{ $project['date'] }}
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <i class="bi bi-people-fill"></i>
                        {{ $project['members'] }} membros
                    </span>
                </div>
            </div>

        </div>
    </a>

    <div class="flex items-center justify-end gap-2 px-6 pb-4 pt-4 border-t border-slate-100">
        <a href="{{ route('projects.forum', $project['id']) }}"
           class="inline-flex h-10 items-center gap-2 rounded-xl border border-atlas-200 bg-atlas-50 px-3 py-2 text-xs font-semibold text-atlas-700 hover:bg-atlas-100 hover:border-atlas-300">
            <i class="bi bi-chat-left-text"></i>
            Fórum
        </a>

        @if ($isCoordinator)
            <button
                type="button"
                class="archive-btn inline-flex h-10 items-center gap-1 rounded-xl border px-3 py-2 text-xs font-semibold {{ $isArchived ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-slate-200 text-slate-700 hover:bg-slate-50' }}"
                data-project-id="{{ $project['id'] }}"
                data-archived="{{ $isArchived ? '1' : '0' }}"
                data-archive-url="{{ route('projects.archive', $project['id']) }}"
                data-unarchive-url="{{ route('projects.unarchive', $project['id']) }}"
            >
                @if ($isArchived)
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Desarquivar</span>
                @else
                    <i class="bi bi-archive"></i>
                    <span>Arquivar</span>
                @endif
            </button>
        @endif

        <button
            type="button"
            class="favorite-btn inline-flex h-10 items-center gap-1 rounded-xl border px-3 py-2 text-xs font-semibold {{ $isFavorite ? 'border-pink-200 text-pink-600 hover:bg-pink-50' : 'border-slate-200 text-slate-700 hover:bg-slate-50' }}"
            data-project-id="{{ $project['id'] }}"
        >
            @if($isFavorite)
                <i class="bi bi-heart-fill text-pink-500 text-lg"></i>
                <span>Favorito</span>
            @else
                <i class="bi bi-heart text-slate-400 text-lg"></i>
                <span>Favoritar</span>
            @endif
        </button>
    </div>

</div>
