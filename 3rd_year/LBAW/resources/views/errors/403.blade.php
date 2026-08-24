@php
    $dashboardUrl = auth()->check()
        ? route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'dashboard')
        : null;

    $actions = array_filter([
        $dashboardUrl ? [
            'label' => 'Ir para o dashboard',
            'url' => $dashboardUrl,
            'style' => 'primary',
        ] : null,
        [
            'label' => 'Voltar à página inicial',
            'url' => route('home'),
            'style' => 'ghost',
        ],
    ]);
@endphp

@include('errors.template', [
    'code' => 403,
    'title' => 'Acesso não permitido',
    'message' => 'Não tens permissões para ver esta secção. Fala com o administrador da conta ou volta ao dashboard.',
    'actions' => $actions,
])
