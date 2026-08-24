@php
    $code = $code ?? '500';
    $title = $title ?? 'Ocorreu um problema';
    $message = $message ?? 'Estamos a trabalhar para resolver. Tenta novamente em breve.';
    $actions = $actions ?? [];
@endphp

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} · {{ config('app.name', 'Atlas') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        atlas: {
                            50: '#f6f7fb',
                            500: '#724e90',
                            900: '#0c1120',
                        },
                    },
                    fontFamily: {
                        sans: ['"Instrument Sans"', 'ui-sans-serif', 'system-ui'],
                    },
                },
            },
        };
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 text-slate-900">
    <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12">
        <div class="w-full max-w-2xl rounded-3xl border border-slate-200 bg-white/70 p-8 text-center shadow-xl backdrop-blur">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">{{ config('app.name', 'Atlas') }}</p>
            <p class="mt-6 text-7xl font-bold text-slate-900">{{ $code }}</p>
            <h1 class="mt-4 text-3xl font-semibold text-slate-900">{{ $title }}</h1>
            <p class="mt-3 text-base text-slate-600">{{ $message }}</p>

            @if (!empty($details))
                <p class="mt-2 text-sm text-slate-500">{{ $details }}</p>
            @endif

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @forelse ($actions as $action)
                    @php
                        $href = $action['url'] ?? '#';
                        $style = $action['style'] ?? 'primary';
                        $classes = $style === 'ghost'
                            ? 'border-slate-200 text-slate-700 hover:border-atlas-200 hover:text-atlas-600'
                            : 'border-atlas-500 bg-atlas-500 text-white hover:bg-atlas-600 hover:border-atlas-600';
                    @endphp
                    <a href="{{ $href }}" class="inline-flex items-center gap-2 rounded-full border px-5 py-2 text-sm font-semibold transition {{ $classes }}">
                        {{ $action['label'] }}
                        <i class="bi bi-arrow-up-right"></i>
                    </a>
                @empty
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-full border border-atlas-500 bg-atlas-500 px-5 py-2 text-sm font-semibold text-white hover:bg-atlas-600">
                        Voltar à página inicial
                        <i class="bi bi-arrow-up-right"></i>
                    </a>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>
