@php
    $dashboardUrl = auth()->check()
        ? route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'dashboard')
        : null;

    $actions = array_filter([
        [
            'label' => 'Ir para a página inicial',
            'url' => route('home'),
            'style' => 'primary',
        ],
        $dashboardUrl ? [
            'label' => 'Abrir o meu dashboard',
            'url' => $dashboardUrl,
            'style' => 'ghost',
        ] : null,
    ]);
@endphp

@include('errors.template', [
    'code' => 404,
    'title' => 'Não encontrámos esta página',
    'message' => 'O endereço pode ter sido alterado ou já não existe. Verifica o link partilhado ou regressa às áreas principais.',
    'actions' => $actions,
])
