<div
    id="add-member-modal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm hidden"
>
    <div class="mx-auto w-full max-w-xl space-y-6">

        <form method="POST"
              action="{{ route('projects.members.add', $project) }}"
              class="space-y-6 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">

            @csrf

            <fieldset class="space-y-4 border-0 p-0 m-0">
                <legend class="sr-only">Adicionar membros</legend>

                <div>
                    <p class="text-sm text-slate-500">Novo membro</p>
                    <h1 class="text-3xl font-semibold text-slate-900">Adicionar membro</h1>
                    <p class="mt-2 text-sm text-slate-500">
                        Insere os emails dos utilizadores que queres adicionar ao projeto.
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-900" for="addMemberEmailInput">Emails</label>

                    <div class="mt-2 flex gap-2">
                        <input
                            type="email"
                            id="addMemberEmailInput"
                            placeholder="up20231234@fe.up.pt"
                            class="flex-1 rounded-2xl border border-slate-200 px-4 py-2 text-sm"
                        >
                        <button type="button"
                                id="addMemberToListBtn"
                                class="rounded-2xl bg-atlas-500 text-white px-4 py-2 hover:bg-atlas-600">
                            Adicionar
                        </button>
                    </div>

                    <input type="hidden" id="addMembersHidden" name="members">
                    <ul id="addMemberList" class="mt-3 space-y-2"></ul>
                </div>
            </fieldset>

            <div class="flex justify-end gap-2">
                <button type="button"
                    data-close-modal
                    class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">
                    Cancelar
                </button>

                <button type="submit"
                    class="rounded-2xl bg-atlas-500 px-4 py-2 text-sm font-semibold text-white hover:bg-atlas-900">
                    Adicionar ao projeto
                </button>
            </div>

        </form>
    </div>
</div>
