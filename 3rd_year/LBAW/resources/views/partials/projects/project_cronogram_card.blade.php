<div class="rounded-2xl border border-slate-200 bg-white p-5">
    <h3 class="text-lg font-semibold text-slate-900">Progresso</h3>

    <ul class="mt-4 space-y-2 text-sm text-slate-600">
        <li class="flex items-center justify-between">
            <span>Início</span>
            <span>{{ $summary['start_date'] ?? 'N/A' }}</span>
        </li>
    </ul>

    <div class="mt-4">
        <p class="text-xs uppercase text-slate-500">Progresso no tempo</p>
        <div class="mt-2 h-2 rounded-full bg-slate-200">
            <div class="h-2 rounded-full bg-atlas-500"
                 style="width: {{ min($summary['progress'], 100) }}%"></div>
        </div>
        <p class="mt-2 text-xs text-slate-500">Baseado nas tarefas concluídas.</p>
    </div>
</div>
