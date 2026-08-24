<section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <p class="text-sm text-slate-500">Projetos ativos</p>
        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['total_projects'] }}</p>
        <p class="text-xs text-slate-400">Projetos em que estás envolvido</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <p class="text-sm text-slate-500">Tarefas concluídas</p>
        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['completed_tasks'] }}</p>
        <p class="text-xs text-slate-400">Último snapshot da base de dados</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <p class="text-sm text-slate-500">Tarefas ativas</p>
        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $stats['active_tasks'] }}</p>
        <p class="text-xs text-slate-400">Tarefas com estado diferente de Done</p>
    </div>

</section>
