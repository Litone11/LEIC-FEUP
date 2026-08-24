<div
    id="create-modal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm hidden"
>
     <div class="mx-auto w-full max-w-3xl space-y-6">
    

                <form method="POST" action="{{ route('projects.store') }}" class="space-y-6 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    @csrf

                    <fieldset class="space-y-6 border-0 p-0 m-0">
                        <legend class="sr-only">Detalhes do projeto</legend>

                        <div>
                            <p class="text-sm text-slate-500">Novo projeto</p>
                            <h1 class="text-3xl font-semibold text-slate-900">Adicionar projeto</h1>
                            <p class="mt-2 text-sm text-slate-500">Define o nome do projeto e uma descrição curta. Ficas automaticamente associado como membro.</p>
                        </div>

                        <div>
                            <label for="name" class="text-sm font-medium text-slate-900">Nome</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Ex: Redesign da app mobile"
                                required
                                maxlength="80"
                                class="mt-2 w-full rounded-2xl border {{ $errors->has('name') ? 'border-rose-400 focus:ring-rose-500' : 'border-slate-200 focus:ring-atlas-500' }} bg-white px-4 py-3 text-slate-900 shadow-sm focus:outline-none focus:ring-2"
                            >
                            @error('name')
                                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="text-sm font-medium text-slate-900">Descrição</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                maxlength="256"
                                required
                                placeholder="Objetivo curto e claro para este projeto"
                                class="mt-2 w-full rounded-2xl border {{ $errors->has('description') ? 'border-rose-400 focus:ring-rose-500' : 'border-slate-200 focus:ring-atlas-500' }} bg-white px-4 py-3 text-slate-900 shadow-sm focus:outline-none focus:ring-2"
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </fieldset>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label for="task_groups" class="text-sm font-medium text-slate-900">Grupos de tarefas e Cor do Projeto</label>
                            <p class="mt-2 text-xs text-slate-500">Adiciona-os depois nas definições do projeto.</p>
                        </div>
                    </div>

                    <fieldset class="border-0 p-0 m-0 space-y-2">
                        <legend class="sr-only">Convidar membros</legend>
                        <label for="memberEmailInput" class="text-sm font-medium text-slate-900">Convidar membros</label>
                        <p class="mt-1 text-xs text-slate-500">Insere emails e adiciona-os à equipa.</p>

                        <div class="mt-2 flex gap-2">
                            <input
                                type="email"
                                id="memberEmailInput"
                                placeholder="up20231234@fe.up.pt"
                                class="flex-1 rounded-2xl border border-slate-200 px-4 py-2 text-sm"
                            >
                            <button type="button" id="addMemberBtn"
                                class="rounded-2xl bg-atlas-500 text-white px-4 py-2 hover:bg-atlas-600">
                                Adicionar
                            </button>
                        </div>

                        <!--isto guarda a arrau dos emails  emails -->
                        <input type="hidden" id="members" name="members">

                        <ul id="memberList" class="mt-3 space-y-2"></ul>
                    </fieldset>


                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('projects') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-atlas-500 px-4 py-2 text-sm font-semibold text-white hover:bg-atlas-900">Criar projeto</button>
                    </div>
                </form>
            </div>
</div>
