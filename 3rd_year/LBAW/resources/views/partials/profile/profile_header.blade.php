<div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
        <img
            src="{{ $profilePicUrl }}"
            alt="Fotografia de perfil de {{ $user->username }}"
            class="h-20 w-20 rounded-3xl object-cover ring-4 ring-white shadow-lg"
        >
        <div class="flex flex-col gap-1">
            <p class="text-sm text-slate-500">Área pessoal</p>
            <h1 class="text-3xl font-semibold text-slate-900">Perfil e preferências</h1>
            <p class="text-sm text-slate-500">Mantém os teus dados atualizados.</p>
        </div>
    </div>

    <div class="flex gap-2 mt-2 sm:mt-0">
        <button type="button"
                data-open-modal="profile-modal"
                class="rounded-2xl bg-atlas-500 text-white px-4 py-2 hover:bg-atlas-600 text-sm font-semibold">
            Editar Perfil
        </button>

        <button type="button"
                data-open-modal="profile_delete_confirmation_modal"
                class="rounded-2xl bg-red-600 text-white px-4 py-2 hover:bg-red-700 text-sm font-semibold">
            Eliminar Conta
        </button>
    </div>
</div>
