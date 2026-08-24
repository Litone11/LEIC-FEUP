<div id="admin_user_modal"
     class="fixed inset-0 hidden z-50 items-center justify-center bg-slate-900/70 backdrop-blur-sm
">
            <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-lg">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar Utilizador</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Estado</p>
                            <p class="text-xs text-slate-500">Bloquear ou desbloquear utilizadores</p>
                        </div>
                        <span id="modalBlockStatus" class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold bg-emerald-50 text-emerald-600">
                            Active
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Username</label>
                        <input type="text" id="modalUsername" class="mt-1 w-full rounded-xl border px-4 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" id="modalEmail" class="mt-1 w-full rounded-xl border px-4 py-2 text-sm">
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Administrador</p>
                            <p class="text-xs text-slate-500">Pode gerir utilizadores e projetos</p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600">
                            <input type="checkbox" id="modalIsAdmin" class="h-4 w-4 rounded border-slate-300 text-atlas-500 focus:ring-atlas-500" >
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Razão para bloqueio</label>
                        <textarea id="modalBlockReason" class="mt-1 w-full rounded-xl border px-4 py-2 text-sm" rows="3" placeholder="Ex: Spam, conduta inadequada"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button id="modalDelete" class="px-4 py-2 rounded-xl border text-rose-800 border-rose-200 hover:bg-rose-50">Eliminar conta</button>
                    <button id="modalBlock" class="px-4 py-2 rounded-xl border text-rose-700 border-rose-200 hover:bg-rose-50">Bloquear</button>
                    <button id="modalUnblock" class="px-4 py-2 rounded-xl border text-emerald-700 border-emerald-200 hover:bg-emerald-50">Desbloquear</button>
                    <div class="flex gap-3">
                        <button id="modalCancel" class="px-4 py-2 rounded-xl border text-slate-700 hover:bg-slate-50">Cancelar</button>
                        <button id="modalSave" class="px-4 py-2 rounded-xl bg-atlas-500 text-white hover:bg-atlas-600">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
