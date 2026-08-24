<div id="profile-modal" data-profile-modal class="fixed inset-0 z-50 hidden flex items-start justify-center overflow-y-auto bg-slate-900/70 backdrop-blur-sm px-4 py-6 sm:px-6 md:items-center">
    <div data-profile-modal-card class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl sm:my-8 max-h-[90vh] overflow-y-auto">

        {{-- ============= SECÇÃO DE EDITAR PERFIL ============= --}}
        <div data-profile-section>

            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Editar perfil</p>
                    <h2 class="text-xl font-semibold text-slate-900">Atualiza os teus dados</h2>
                </div>
                <button data-close-modal class="rounded-full bg-slate-100 px-3 py-1 text-slate-600 hover:bg-slate-200">Fechar</button>

            </div>

            @php
                $profileStatus = old('status', $user->regularProfile?->status ?? 'offline');
                $profileCustomStatus = old('custom_status', $user->regularProfile?->custom_status ?? null);
            @endphp

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                @method('PATCH')

                {{-- FOTO --}}
                <div>
                    <span class="text-sm font-medium text-slate-900">Foto de perfil</span>

                    <div class="mt-3 flex flex-wrap items-center gap-4">
                        <img
                            src="{{ $profilePicUrl }}"
                            alt="Foto de {{ $user->username }}"
                            class="h-16 w-16 rounded-full object-cover ring-2 ring-slate-200"
                            data-profile-picture-preview
                            data-profile-picture-default="{{ $defaultProfilePicUrl }}"
                        >

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                            <label for="profile-picture-input"
                                   class="inline-flex cursor-pointer items-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                Selecionar imagem
                                <input id="profile-picture-input" type="file" name="profile_pic" accept="image/*" class="sr-only">
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

                    <input type="hidden" name="remove_profile_pic" value="0" data-remove-profile-picture-input>
                </div>

                {{-- USERNAME --}}
                <div>
                    <label class="text-sm font-medium text-slate-900" for="profile-username">Nome de utilizador</label>
                    <input
                        id="profile-username"
                        type="text"
                        name="username"
                        value="{{ old('username', $user->username) }}"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2"
                    >
                </div>

                {{-- EMAIL --}}
                <div>
                    <label class="text-sm font-medium text-slate-900" for="profile-email">Email</label>
                    <input
                        id="profile-email"
                        type="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2"
                    >
                </div>
                
                {{-- STATUS --}}
                <div>
                    <label class="text-sm font-medium text-slate-900" for="profile-status">
                        Estado
                    </label>

                    <select
                        id="profile-status"
                        name="status"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2"
                        data-status-select
                    >
                        <option value="disponível" {{ $profileStatus === 'disponível' ? 'selected' : '' }}>
                            Disponível
                        </option>
                        <option value="offline" {{ $profileStatus === 'offline' ? 'selected' : '' }}>
                            Offline
                        </option>
                        <option value="customizável" {{ $profileStatus === 'customizável' ? 'selected' : '' }}>
                            Personalizado
                        </option>
                    </select>
                </div>

                {{-- CUSTOM STATUS --}}
                <div
                    data-custom-status-wrapper
                    class="{{ $profileStatus === 'customizável' ? '' : 'hidden' }}"
                >
                    <label class="text-sm font-medium text-slate-900" for="profile-custom-status">
                        Estado personalizado
                    </label>

                    <input
                        id="profile-custom-status"
                        type="text"
                        name="custom_status"
                        maxlength="60"
                        value="{{ $profileCustomStatus }}"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2"
                    >
                </div>


                {{-- BOTÃO PARA IR PARA A SECÇÃO DE PASSWORD --}}
                <button
                    type="button"
                    data-open-password-section
                    class="w-full rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Alterar password
                </button>

                {{-- GUARDAR ALTERAÇÕES --}}
                <div class="flex justify-end pt-2">
                    <button type="submit"
                            class="rounded-2xl bg-atlas-500 px-5 py-2 text-sm font-semibold text-white hover:bg-atlas-900">
                        Guardar alterações
                    </button>
                </div>
            </form>
        </div>


        {{-- ============= SECÇÃO DE PASSWORD ============= --}}
      <div data-password-section class="hidden">

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-slate-900">Alterar password</h2>

        <button type="button"
                data-back-to-profile
                class="rounded-full bg-slate-100 px-3 py-1 text-slate-600 hover:bg-slate-200">
            Voltar
        </button>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        <input type="hidden" name="username" value="{{ $user->username }}">
        <input type="hidden" name="email" value="{{ $user->email }}">
        <input type="hidden" name="status" value="{{ $profileStatus }}">
        <input type="hidden" name="custom_status" value="{{ $profileCustomStatus }}">
        @csrf
        @method('PATCH')

        {{-- CURRENT PASSWORD --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-900" for="profile-current-password">
                Password atual
            </label>

            <div class="relative">
                <input id="profile-current-password"
                       type="password"
                       name="current_password"
                       autocomplete="current-password"
                       class="w-full rounded-2xl border px-4 py-2 pr-12 text-slate-900 focus:outline-none focus:ring-2
                           @error('current_password') border-rose-500 focus:ring-rose-400 @else border-slate-200 focus:ring-atlas-500 @enderror">

                <button type="button"
                        class="absolute inset-y-0 right-0 mr-3 flex items-center text-slate-500 hover:text-slate-800"
                        data-password-visibility-toggle
                        data-target="profile-current-password">
                    <i class="bi bi-eye-slash text-lg"></i>
                </button>
            </div>

            <p class="mt-1 text-xs text-slate-500">
                Obrigatória para alterar a password.
            </p>

            @error('current_password')
                <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- NEW PASSWORD --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-900" for="profile-password">Nova password</label>

            <div class="relative">
                <input id="profile-password"
                       type="password"
                       name="password"
                       autocomplete="new-password"
                       minlength="8"
                       pattern="(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).*"
                       class="w-full rounded-2xl border px-4 py-2 pr-12 text-slate-900 focus:outline-none focus:ring-2
                           @error('password') border-rose-500 focus:ring-rose-400 @else border-slate-200 focus:ring-atlas-500 @enderror">

                <button type="button"
                        class="absolute inset-y-0 right-0 mr-3 flex items-center text-slate-500 hover:text-slate-800"
                        data-password-visibility-toggle
                        data-target="profile-password">
                    <i class="bi bi-eye-slash text-lg"></i>
                </button>
            </div>

            <p class="text-xs text-slate-500">
                Deve incluir: mín. 8 caracteres, 1 maiúscula, 1 minúscula, 1 número e 1 símbolo.
            </p>

            {{-- PASSWORD CRITERIA --}}
            <ul class="mt-2 space-y-1 text-xs" data-password-criteria>
                <li data-criterion="length" class="flex items-center gap-2 text-slate-500">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                    Mínimo 8 caracteres
                </li>
                <li data-criterion="uppercase" class="flex items-center gap-2 text-slate-500">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                    Pelo menos uma letra maiúscula
                </li>
                <li data-criterion="lowercase" class="flex items-center gap-2 text-slate-500">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                    Pelo menos uma letra minúscula
                </li>
                <li data-criterion="number" class="flex items-center gap-2 text-slate-500">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                    Pelo menos um número
                </li>
                <li data-criterion="symbol" class="flex items-center gap-2 text-slate-500">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                    Pelo menos um símbolo
                </li>
            </ul>

            @error('password')
                <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- CONFIRM PASSWORD --}}
        <div>
            <label class="text-sm font-medium text-slate-900" for="profile-password-confirmation">
                Confirmar password
            </label>

            <input id="profile-password-confirmation"
                   type="password"
                   name="password_confirmation"
                   autocomplete="new-password"
                   class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2 text-slate-900 focus:outline-none focus:ring-2 focus:ring-atlas-500">
        </div>

        {{-- SUBMIT --}}
        <div class="flex justify-end pt-2">
            <button type="submit"
                    class="rounded-2xl bg-atlas-500 px-5 py-2 text-sm font-semibold text-white hover:bg-atlas-900">
                Guardar nova password
            </button>
        </div>
    </form>
</div>


    </div>
</div>
