<div id="invite-member-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm hidden">
    <div class="mx-auto w-full max-w-md space-y-6"> {{-- fixed width --}}
        <form method="POST" action="{{ route('projects.members.invite', $project) }}"
              class="space-y-6 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            @csrf
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Convidar membros por email</h1>
                <p class="mt-2 text-sm text-slate-500">O convidado recebe um email e uma notificação no Atlas para aceitar ou rejeitar o pedido.</p>
            </div>

            @if (session('error'))
                <div class="flex items-start gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                    <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
                    <p class="leading-snug">{{ session('error') }}</p>
                </div>
            @endif

            <div>
                <label for="inviteEmail" class="text-sm font-medium text-slate-900">Email</label>
                <input name="email" id="inviteEmail" type="email" required
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-900 shadow-sm focus:outline-none focus:ring-2 @error('email') border-rose-400 @enderror"
                    placeholder="alice@example.com"
                    value="{{ old('email') }}">
                @error('email')
                    <p class="mt-1 flex items-center gap-1 text-sm text-rose-600">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" data-close-modal
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">
                    Cancelar
                </button>
                <button type="submit"
                        class="rounded-2xl bg-atlas-500 px-4 py-2 text-sm font-semibold text-white hover:bg-atlas-900">
                    Enviar convite
                </button>
            </div>
        </form>
    </div>
</div>
