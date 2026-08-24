@php
    $actions = [
        [
            'label' => 'Voltar a tentar',
            'url' => url()->previous() ?: route('home'),
            'style' => 'primary',
        ],
        [
            'label' => 'Contactar suporte',
            'url' => route('contact'),
            'style' => 'ghost',
        ],
    ];
@endphp

@include('errors.template', [
    'code' => 500,
    'title' => 'Ocorreu um erro inesperado',
    'message' => 'A nossa equipa já recebeu o alerta. Recarrega a página ou regressa mais tarde.',
    'details' => 'Se o problema persistir envia-nos o contexto para acelerarmos a correção.',
    'actions' => $actions,
])
