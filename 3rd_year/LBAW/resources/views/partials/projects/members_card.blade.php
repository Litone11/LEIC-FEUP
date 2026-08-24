<div class="rounded-2xl border border-slate-200 bg-white p-5">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Membros</h3>
            <p class="text-sm text-slate-500">
                {{ $summary['team_count'] }} pessoas na equipa
            </p>
        </div>
    </div>

    <ul class="mt-4 space-y-3 text-sm text-slate-600">
        @forelse ($members as $member)
            <li class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2">

                <span>{{ $member->username }}</span>

                <span class="
                    text-xs font-semibold px-2 py-0.5 rounded-full uppercase
                    @if ($member->pivot->user_role === 'coordinator')
                        bg-purple-100 text-purple-700
                    @else
                        bg-slate-100 text-slate-600
                    @endif
                ">
                    {{ $member->pivot->user_role }}
                </span>

            </li>
        @empty
            <li class="text-xs text-slate-400">Sem membros registados.</li>
        @endforelse
    </ul>

    <div class="mt-4 flex items-center justify-between">
        @if ($userRole === 'coordinator')
            <a href="{{ route('projects.members', $project) }}"
            class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-100 border border-amber-300 px-3 py-2 rounded-full hover:bg-amber-200">
                <i class="bi bi-gear-fill text-amber-600"></i>
                Gerir membros
            </a>
        @else
            <a href="{{ route('projects.members', $project) }}"
            class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:border-atlas-500 hover:text-atlas-500">
                Ver mais
            </a>
        @endif
    </div>
</div>
