@php $adminLayout = $isAdmin ?? false; @endphp

<section class="mt-10 grid grid-cols-1 gap-6">

    {{-- PERSONAL INFO --}}
    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm {{ $adminLayout ? 'w-full' : '' }}">
        <header class="mb-6">
            <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Informação pessoal</p>
            <h2 class="text-xl font-semibold text-slate-900">Identidade e contacto</h2>
        </header>

        <dl class="space-y-4 text-sm text-slate-600">
            <div class="rounded-2xl border px-4 py-3">
                <dt class="text-xs font-semibold uppercase text-slate-400">Nome de utilizador</dt>
                <dd class="text-base font-medium text-slate-900">{{ $user->username }}</dd>
            </div>

            <div class="rounded-2xl border px-4 py-3">
                <dt class="text-xs font-semibold uppercase text-slate-400">Email</dt>
                <dd class="text-base font-medium text-slate-900">{{ $user->email }}</dd>
            </div>

            <div class="rounded-2xl border px-4 py-3">
                <dt class="text-xs font-semibold uppercase text-slate-400">Membro desde</dt>
                <dd class="text-base font-medium text-slate-900">{{ $joinedAt ?? '—' }}</dd>
            </div>
        </dl>
    </article>

</section>
