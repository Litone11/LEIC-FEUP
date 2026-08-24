<div id="admin_new_account_modal"
     class="fixed inset-0 hidden z-50 items-center justify-center bg-slate-900/70 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-lg">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Novo Utilizador</h3>

        <form method="POST" action="{{ route('admin.users.create') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label>Username</label>
                    <input required type="text" name="username" class="mt-1 w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label>Email</label>
                    <input required type="email" name="email" class="mt-1 w-full border rounded px-3 py-2">
                </div>

                <div>
                    <label>Password</label>
                    <input required type="password" name="password" class="mt-1 w-full border rounded px-3 py-2">
                </div>

                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" id="isAdmin" name="isAdmin" value="1">
                    <label for="isAdmin">Administrador?</label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" id="newAccountmodalCancel" data-close-modal class="px-4 py-2 rounded-xl border text-slate-700 hover:bg-slate-50">Cancelar</button>
                <button type="submit" id="newAccountmodalSave" class="px-4 py-2 rounded-xl bg-atlas-500 text-white hover:bg-atlas-600">Guardar</button>
            </div>
        </form>
    </div>
</div>
