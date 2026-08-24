<section class="mt-10">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-slate-900">Projetos recentes</h2>
        <a href="{{ route('projects') }}" class="text-sm font-medium text-atlas-500 hover:text-atlas-900">Ver todos</a>
    </div>

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white">
        <div class="divide-y divide-slate-100">
            @forelse ($recentProjects as $project)
                <div class="flex flex-col sm:flex-row sm:justify-between px-6 py-4">
                    <div>
                        <p class="font-semibold">{{ $project['name'] }}</p>
                        <p class="text-sm text-slate-500">{{ \Illuminate\Support\Str::limit($project['description'], 90) }}</p>
                    </div>
                    <span class="text-xs text-slate-400">{{ $project['created_at'] ?? '—' }}</span>
                </div>
            @empty
                <p class="px-6 py-8 text-center text-slate-500">Sem projetos recentes.</p>
            @endforelse
        </div>
    </div>
</section>
