@if ($errors->any())
    <div class="mx-auto mb-6 max-w-3xl rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
        <div class="flex items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill mt-0.5 text-rose-600"></i>
            <div>
                <p class="font-semibold text-rose-900">Corrige os seguintes pontos antes de continuar:</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-rose-600">
                    Se precisares de mais contexto, consulta a ajuda contextual junto ao canto inferior direito.
                </p>
            </div>
        </div>
    </div>
@endif
