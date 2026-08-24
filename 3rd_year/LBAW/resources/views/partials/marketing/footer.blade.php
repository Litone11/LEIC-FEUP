<footer class="border-t border-slate-200 bg-white/90 mt-12">
    <div class="mx-auto max-w-6xl px-6 py-10 grid gap-8 md:grid-cols-[1.2fr_1fr]">
        <div class="space-y-3">
            <div class="flex items-center gap-2 text-xl font-semibold text-slate-900">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">A</span>
                Atlas
            </div>
            <p class="text-sm text-slate-600">
                Mantém equipas alinhadas e o trabalho visível. Simples, direto e com o essencial para entregar.
            </p>
            <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-xs font-semibold text-indigo-700">
                <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                Foco, clareza e ritmo
            </div>
        </div>
        <div class="grid gap-6 sm:grid-cols-3">
            <div class="space-y-2 text-sm">
                <p class="font-semibold text-slate-900">Navegação</p>
                <a href="{{ route('home') }}" class="block text-slate-600 hover:text-slate-900">Home</a>
                <a href="{{ route('about') }}" class="block text-slate-600 hover:text-slate-900">Sobre nós</a>
                <a href="{{ route('features') }}" class="block text-slate-600 hover:text-slate-900">Funcionalidades</a>
                <a href="{{ route('contact') }}" class="block text-slate-600 hover:text-slate-900">Contactos</a>
            </div>
            <div class="space-y-2 text-sm">
                <p class="font-semibold text-slate-900">Conta</p>
                @auth
                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="block text-slate-600 hover:text-slate-900">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="block text-slate-600 hover:text-slate-900">Login</a>
                    <a href="{{ route('register') }}" class="block text-slate-600 hover:text-slate-900">Criar conta</a>
                @endauth
            </div>
            <div class="space-y-2 text-sm">
                <p class="font-semibold text-slate-900">Contacto</p>
                <p class="text-slate-600">support@atlasapp.test</p>
                <p class="text-slate-600">+351 210 000 000</p>
            </div>
        </div>
    </div>
</footer>
