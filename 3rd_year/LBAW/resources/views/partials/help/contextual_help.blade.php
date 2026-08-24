<div class="fixed bottom-6 right-6 z-40 text-sm" data-contextual-help>
    <button type="button"
            data-help-toggle
            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 font-semibold text-slate-700 shadow hover:border-atlas-200 hover:text-atlas-600">
        <i class="bi bi-life-preserver text-atlas-500"></i>
        Precisas de ajuda?
    </button>

    <div class="mt-3 hidden w-80 rounded-3xl border border-slate-200 bg-white p-5 shadow-xl" data-help-panel>
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ajuda contextual</p>
                <h3 class="text-lg font-semibold text-slate-900">{{ $topic['title'] ?? 'Como funciona?' }}</h3>
            </div>
            <button type="button"
                    data-help-close
                    class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        @if (!empty($topic['description']))
            <p class="mt-3 text-sm text-slate-600">{{ $topic['description'] }}</p>
        @endif

        @if (!empty($topic['tips']))
            <ul class="mt-4 space-y-3 text-sm text-slate-600">
                @foreach ($topic['tips'] as $tip)
                    <li class="rounded-2xl border border-slate-100 bg-slate-50 p-3">
                        <p class="font-semibold text-slate-900">{{ $tip['title'] }}</p>
                        <p class="mt-1 text-slate-600">{{ $tip['body'] }}</p>
                    </li>
                @endforeach
            </ul>
        @endif

        @if (!empty($topic['links']))
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($topic['links'] as $link)
                    @php
                        $url = $link['url'] ?? (isset($link['route'])
                            ? route($link['route'], $link['params'] ?? [])
                            : '#');
                    @endphp
                    <a href="{{ $url }}"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-atlas-600 hover:border-atlas-200 hover:text-atlas-700">
                        {{ $link['label'] }}
                        <i class="bi bi-arrow-up-right"></i>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
