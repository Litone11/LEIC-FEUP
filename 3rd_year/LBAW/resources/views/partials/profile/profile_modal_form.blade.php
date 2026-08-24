<div class="flex items-start justify-between gap-4">
    <div>
        <p class="text-sm text-slate-500">Editar perfil</p>
        <h2 class="text-xl font-semibold text-slate-900">Atualiza os teus dados</h2>
    </div>
    <button type="button"
            data-close-profile-modal
            class="rounded-full bg-slate-100 px-3 py-1 text-slate-600 hover:bg-slate-200">
        Fechar
    </button>
</div>

<form method="POST"
      action="{{ route('profile.update') }}"
      enctype="multipart/form-data"
      class="mt-6 space-y-4">
    @csrf

    {{-- FOTO DE PERFIL --}}
    <div>
        <span class="text-sm font-medium text-slate-900">Foto de perfil</span>

        <div class="mt-3 flex flex-wrap items-center gap-4">
            <img
                src="{{ $profilePicUrl }}"
                alt="Pré-visualização da foto de {{ $user->username }}"
                class="h-16 w-16 rounded-full object-cover ring-2 ring-slate-200"
                data-profile-picture-preview
                data-profile-picture-default="{{ $defaultProfilePicUrl }}"
            >

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <label for="profile-picture-input"
                       class="inline-flex cursor-pointer items-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Selecionar imagem
                    <input
                        id="profile-picture-input"
                        type="file"
                        name="profile_pic"
                        accept="image/*"
                        class="sr-only"
                    >
                </label>

                <button
                    type="button"
                    data-remove-profile-picture
                    class="inline-flex items-center rounded-2xl border border-rose-200 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 {{ $canRemoveProfilePic ? '' : 'opacity-50 cursor-not-allowed' }}"
                    {{ $canRemoveProfilePic ? '' : 'disabled' }}
                >
                    Remover foto
                </button>
            </div>
        </div>

        <input type="hidden"
               name="remove_profile_pic"
               value="{{ old('remove_profile_pic', '0') }}"
               data-remove-profile-picture-input>

        <p class="mt-2 text-xs text-slate-500">
            Formatos permitidos: JPG, PNG, GIF, SVG ou WebP até 2MB.
        </p>

        @error('profile_pic')
            <p class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    {{-- USERNAME --}}
    <div>
        <label class="text-sm font-medium text-slate-900" for="profile-username">Nome de utilizador</label>
        <input
            id="profile-username"
            type="text"
            name="username"
            value="{{ old('username', $user->username) }}"
            maxlength="80"
            autocomplete="username"
            @class([
                'mt-2 w-full rounded-2xl border px-4 py-2 text-slate-900 focus:outline-none focus:ring-2',
                'border-rose-500 focus:ring-rose-400' => $errors->has('username'),
                'border-slate-200 focus:ring-atlas-500' => ! $errors->has('username'),
            ])
        >
        @error('username')
            <p class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    {{-- EMAIL --}}
    <div>
        <label class="text-sm font-medium text-slate-900" for="profile-email">Email</label>
        <input
            id="profile-email"
            type="email"
            name="email"
            value="{{ old('email', $user->email) }}"
            autocomplete="email"
            @class([
                'mt-2 w-full rounded-2xl border px-4 py-2 text-slate-900 focus:outline-none focus:ring-2',
                'border-rose-500 focus:ring-rose-400' => $errors->has('email'),
                'border-slate-200 focus:ring-atlas-500' => ! $errors->has('email'),
            ])
        >
        @error('email')
            <p class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
        @enderror
    </div>

    {{-- PASSWORD SECTION --}}
    <div class="rounded-2xl border border-slate-200 px-4 py-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-900">Password</p>
                <p class="text-xs text-slate-500">
                    Mantemos a password atual, a menos que peças para alterar.
                </p>
            </div>

            <button
                type="button"
                data-password-toggle
                class="rounded-2xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 {{ $showPasswordFields ? 'hidden' : '' }}"
            >
                Alterar password
            </button>
        </div>

        <div
            data-password-section
            class="mt-4 space-y-4 {{ $showPasswordFields ? '' : 'hidden' }}"
        >
            {{-- CURRENT PASSWORD --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-900" for="profile-current-password">
                    Password atual
                </label>
                <div class="relative">
                    <input
                        id="profile-current-password"
                        type="password"
                        name="current_password"
                        placeholder="Confirma a tua password atual"
                        autocomplete="current-password"
                        @class([
                            'w-full rounded-2xl border px-4 py-2 pr-12 text-slate-900 focus:outline-none focus:ring-2',
                            'border-rose-500 focus:ring-rose-400' => $errors->has('current_password'),
                            'border-slate-200 focus:ring-atlas-500' => ! $errors->has('current_password'),
                        ])
                    >
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 mr-3 flex items-center text-slate-500 hover:text-slate-800"
                        data-password-visibility-toggle
                        data-target="profile-current-password"
                        aria-pressed="false"
                        aria-label="Mostrar password atual"
                    >
                        <i class="bi bi-eye-slash text-lg" aria-hidden="true"></i>
                        <span class="sr-only">Alternar visibilidade</span>
                    </button>
                </div>
                <p class="mt-2 text-xs text-slate-500">
                    Obrigatória apenas quando defines uma nova password.
                </p>
                @error('current_password')
                    <p class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- NEW PASSWORD --}}
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-900" for="profile-password">
                    Nova password
                </label>
                <div class="relative">
                    <input
                        id="profile-password"
                        type="password"
                        name="password"
                        placeholder="Mín. 8, maiúsculas, minúsculas, número e símbolo"
                        autocomplete="new-password"
                        minlength="8"
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}"
                        @class([
                            'w-full rounded-2xl border px-4 py-2 pr-12 text-slate-900 focus:outline-none focus:ring-2',
                            'border-rose-500 focus:ring-rose-400' => $errors->has('password'),
                            'border-slate-200 focus:ring-atlas-500' => ! $errors->has('password'),
                        ])
                    >
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 mr-3 flex items-center text-slate-500 hover:text-slate-800"
                        data-password-visibility-toggle
                        data-target="profile-password"
                        aria-pressed="false"
                        aria-label="Mostrar nova password"
                    >
                        <i class="bi bi-eye-slash text-lg" aria-hidden="true"></i>
                        <span class="sr-only">Alternar visibilidade</span>
                    </button>
                </div>

                <p class="mt-2 text-xs text-slate-500">
                    Deixa em branco se não precisas de mudar a password.
                </p>

                <ul class="mt-2 space-y-1 text-xs" data-password-criteria>
                    <li class="flex items-center gap-2 text-slate-500" data-criterion="length">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                        Mínimo 8 caracteres
                    </li>
                    <li class="flex items-center gap-2 text-slate-500" data-criterion="uppercase">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                        Pelo menos uma letra maiúscula
                    </li>
                    <li class="flex items-center gap-2 text-slate-500" data-criterion="lowercase">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                        Pelo menos uma letra minúscula
                    </li>
                    <li class="flex items-center gap-2 text-slate-500" data-criterion="number">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                        Pelo menos um número
                    </li>
                    <li class="flex items-center gap-2 text-slate-500" data-criterion="symbol">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                        Pelo menos um símbolo
                    </li>
                </ul>

                @error('password')
                    <p class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            {{-- CONFIRM PASSWORD --}}
            <div>
                <label class="text-sm font-medium text-slate-900" for="profile-password-confirmation">
                    Confirmar password
                </label>
                <input
                    id="profile-password-confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirma a nova password"
                    autocomplete="new-password"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-slate-900 focus:outline-none focus:ring-2 focus:ring-atlas-500"
                >
            </div>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="flex justify-end gap-2 pt-2">
        <button type="button"
                data-close-profile-modal
                class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">
            Cancelar
        </button>
        <button type="submit"
                class="rounded-2xl bg-atlas-500 px-5 py-2 text-sm font-semibold text-white hover:bg-atlas-900">
            Guardar alterações
        </button>
    </div>
</form>
