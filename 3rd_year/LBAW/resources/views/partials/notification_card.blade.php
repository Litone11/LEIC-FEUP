@props(['notification'])
@php
    $payload = null;
    if (!empty($notification->message_)) {
        $decoded = json_decode($notification->message_, true);
        if (is_array($decoded) && ($decoded['t'] ?? null) === 'invite') {
            $payload = $decoded;
        }
    }
    $expires = ($notification->created_at ?? now())->copy()->addDays(3);
    $isRead = (bool) $notification->is_read;
@endphp

<li class="group flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm {{ $isRead ? 'opacity-80' : '' }}">
    <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $payload ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
        <i class="{{ $payload ? 'bi bi-envelope-paper' : 'bi bi-bell-fill' }} text-lg"></i>
    </div>

    <div class="flex-1">
        @if($payload)
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <p class="text-sm font-semibold text-slate-900">
                        Convite para projeto
                    </p>
                    @if(!$isRead)
                        <span class="rounded-full bg-atlas-50 px-2 py-1 text-[11px] font-semibold text-atlas-600">Por ler</span>
                    @endif
                </div>
                <p class="text-sm text-slate-700">
                    <span class="font-semibold">{{ $payload['s'] ?? '—' }}</span> convidou-te para o projeto
                    <span class="font-semibold">{{ $payload['pn'] ?? '—' }}</span>.
                </p>
                @php
                    $acceptUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'projects.invites.accept',
                        $expires,
                        [
                            'p' => $payload['pid'] ?? null,
                            'u' => $notification->receiver_id,
                            'n' => $notification->notification_id,
                        ]
                    );
                    $rejectUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'projects.invites.reject',
                        $expires,
                        [
                            'p' => $payload['pid'] ?? null,
                            'u' => $notification->receiver_id,
                            'n' => $notification->notification_id,
                        ]
                    );
                @endphp
                <div class="flex flex-wrap gap-3 mt-2">
                    <a href="{{ $acceptUrl }}"
                       class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                        <i class="bi bi-check-circle"></i> Aceitar
                    </a>
                    <a href="{{ $rejectUrl }}"
                       class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                        <i class="bi bi-x-circle"></i> Rejeitar
                    </a>
                </div>
            </div>
        @else
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <p class="text-sm font-semibold text-slate-900">
                        {{ $notification->title ?? 'Notificação' }}
                    </p>
                    @if(!$isRead)
                        <span class="rounded-full bg-atlas-50 px-2 py-1 text-[11px] font-semibold text-atlas-600">Por ler</span>
                    @endif
                </div>
                <p class="text-sm text-slate-700 leading-relaxed">
                    {!! $notification->message_ !!}
                </p>
                @if (!empty($notification->link))
                    <a href="{{ $notification->link }}"
                       class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-atlas-600 hover:text-atlas-800">
                        <i class="bi bi-arrow-right"></i> Ver detalhes
                    </a>
                @endif
            </div>
        @endif

        <div class="mt-3 flex items-center gap-3 text-xs text-slate-400">
            <span>{{ $notification->created_at?->format('M d, Y \\· H:i') }}</span>
            @if(!$isRead)
                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                    @csrf
                    <button class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-200" type="submit">
                        <i class="bi bi-check2"></i> Marcar como lida
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('notifications.delete', $notification) }}">
                @csrf
                @method('DELETE')
                <button class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-100" type="submit">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</li>
