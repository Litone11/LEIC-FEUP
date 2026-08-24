<?php

return [
    'topics' => [
        'dashboard' => [
            'title' => 'Navega rapidamente no painel',
            'description' => 'Vê o progresso global, pesquisa projetos e abre tarefas a partir de um único lugar.',
            'tips' => [
                [
                    'title' => 'Pesquisa instantânea',
                    'body' => 'Utiliza o campo no topo para filtrar projetos e tarefas sem sair da página.',
                ],
                [
                    'title' => 'Resultados rápidos',
                    'body' => 'Os resultados aparecem acima do conteúdo assim que começas a escrever.',
                ],
            ],
            'links' => [
                ['label' => 'Abrir lista de projetos', 'route' => 'projects'],
                ['label' => 'Ver o meu perfil', 'route' => 'profile'],
            ],
        ],
        'projects' => [
            'title' => 'Lista de projetos',
            'description' => 'Pesquisa, ordena e cria projetos para acompanhar o estado atual.',
            'tips' => [
                [
                    'title' => 'Pesquisa e ordenação',
                    'body' => 'Combina pesquisa e ordenação para encontrares projetos rapidamente.',
                ],
                [
                    'title' => 'Favoritos',
                    'body' => 'Marca um projeto como favorito para o destacar no painel inicial.',
                ],
                [
                    'title' => 'Arquivar projetos',
                    'body' => 'Usa o botão de arquivo nos cartões para esconder projetos concluídos.',
                ],
            ],
            
        ],
        'projects.show' => [
            'title' => 'Detalhes do projeto',
            'description' => 'Segue tarefas, membros e progresso com informação detalhada.',
            'tips' => [
                [
                    'title' => 'Visão para coordenadores',
                    'body' => 'Coordenadores conseguem ver e editar todas as tarefas; membros apenas as atribuídas.',
                ],
                [
                    'title' => 'Resumo do projeto',
                    'body' => 'Consulta progresso, cronograma e equipa na coluna lateral.',
                ],
            ],
            'links' => [],
        ],
        'profile' => [
            'title' => 'Perfil do utilizador',
            'description' => 'Atualiza dados pessoais, palavra-passe e foto numa única página.',
            'tips' => [
                [
                    'title' => 'Atualiza a foto',
                    'body' => 'Carrega uma nova imagem ou remove a atual no modal de edição.',
                ],
                [
                    'title' => 'Alterar palavra-passe',
                    'body' => 'Usa a secção de segurança para definir uma nova palavra-passe.',
                ],
            ],
            'links' => [
                ['label' => 'Voltar ao dashboard', 'route' => 'dashboard'],
            ],
        ],
        'admin.dashboard' => [
            'title' => 'Painel administrativo',
            'description' => 'Acompanha métricas, gere utilizadores e intervém rapidamente em projetos.',
            'tips' => [
                [
                    'title' => 'Suspender projetos',
                    'body' => 'Usa o botão “Suspender” para bloquear um projeto e guarda o motivo no registo.',
                ],
                [
                    'title' => 'Editar utilizadores',
                    'body' => 'Abre o modal de edição para atualizar permissões, bloquear ou remover contas.',
                ],
            ],
            'links' => [
                ['label' => 'Ver todos os utilizadores', 'route' => 'admin.dashboard', 'params' => ['tab' => 'users']],
            ],
        ],
        'projects.members' => [
            'title' => 'Gestão de membros',
            'description' => 'Pesquisa pessoas e organiza funções dentro do projeto.',
            'tips' => [
                [
                    'title' => 'Pesquisa rápida',
                    'body' => 'Usa o campo de pesquisa para filtrar por nome ou email.',
                ],
                [
                    'title' => 'Modo de gestão',
                    'body' => 'Ativa “Gerir membros” para promover ou remover pessoas (coordenadores).',
                ],
                [
                    'title' => 'Convites',
                    'body' => 'Envia convites por email diretamente a partir do modal.',
                ],
            ],
            'links' => [],
        ],
        'tasks.index' => [
            'title' => 'Tarefas do projeto',
            'description' => 'Cria, organiza e filtra tarefas para manter o trabalho alinhado.',
            'tips' => [
                [
                    'title' => 'Nova tarefa',
                    'body' => 'Usa o botão “Nova tarefa” para abrir o modal de criação.',
                ],
                [
                    'title' => 'Filtros inteligentes',
                    'body' => 'Pesquisa, ordena e muda a ordem para veres o que é mais urgente.',
                ],
                [
                    'title' => 'Listas e grupos',
                    'body' => 'Coordenadores podem criar grupos/listas para organizar o backlog.',
                ],
            ],
            'links' => [],
        ],
        'tasks.show' => [
            'title' => 'Detalhes da tarefa',
            'description' => 'Consulta estado, edita campos e acompanha a conversa.',
            'tips' => [
                [
                    'title' => 'Editar rapidamente',
                    'body' => 'A coluna principal permite editar campos e estado da tarefa.',
                ],
                [
                    'title' => 'Comentários',
                    'body' => 'Usa o painel lateral para acompanhar comentários e dependências.',
                ],
            ],
            'links' => [],
        ],
        'notifications' => [
            'title' => 'Notificações',
            'description' => 'Consulta avisos, convites e atualizações da tua conta.',
            'tips' => [
                [
                    'title' => 'Por ler e lidas',
                    'body' => 'Alterna entre separadores para manter a caixa organizada.',
                ],
                [
                    'title' => 'Marcar como lida',
                    'body' => 'Marca notificações como lidas ou elimina quando já não precisas.',
                ],
            ],
            'links' => [
                ['label' => 'Ver convites', 'route' => 'invitations'],
            ],
        ],
        'invitations' => [
            'title' => 'Convites de projeto',
            'description' => 'Aceita ou recusa convites pendentes para novos projetos.',
            'tips' => [
                [
                    'title' => 'Responder rapidamente',
                    'body' => 'Usa os botões de aceitar/recusar para decidir o convite.',
                ],
            ],
            'links' => [
                ['label' => 'Ver notificações', 'route' => 'notifications'],
            ],
        ],
        'calendar' => [
            'title' => 'Calendário',
            'description' => 'Acompanha prazos e tarefas ao longo do tempo.',
            'tips' => [
                [
                    'title' => 'Filtrar projetos',
                    'body' => 'Seleciona os projetos que queres visualizar no calendário.',
                ],
                [
                    'title' => 'Navegar projetos',
                    'body' => 'Usa as setas para percorrer a lista quando tens muitos projetos.',
                ],
            ],
            'links' => [],
        ],
        'projects.forum' => [
            'title' => 'Fórum do projeto',
            'description' => 'Cria tópicos e acompanha discussões da equipa.',
            'tips' => [
                [
                    'title' => 'Novo tópico',
                    'body' => 'Clica em “Novo tópico” para iniciar uma discussão.',
                ],
                [
                    'title' => 'Tarefas ligadas',
                    'body' => 'Tópicos podem estar ligados a tarefas para dar contexto.',
                ],
            ],
            'links' => [],
        ],
        'projects.forum.topic' => [
            'title' => 'Discussão do tópico',
            'description' => 'Lê, reage e responde às mensagens da equipa.',
            'tips' => [
                [
                    'title' => 'Gostar de tópico',
                    'body' => 'Usa o botão de gosto para destacar conteúdo útil.',
                ],
                [
                    'title' => 'Responder',
                    'body' => 'Escreve uma resposta no final para continuar a conversa.',
                ],
            ],
            'links' => [],
        ],
        'projects.analytics' => [
            'title' => 'Analytics do projeto',
            'description' => 'Consulta a distribuição de esforço por membro.',
            'tips' => [
                [
                    'title' => 'Índice de esforço',
                    'body' => 'Um valor mais alto indica maior carga de trabalho.',
                ],
                [
                    'title' => 'Distribuição',
                    'body' => 'Compara atribuídas vs. responsabilidades para equilibrar a equipa.',
                ],
            ],
            'links' => [],
        ],
        'projects.settings' => [
            'title' => 'Configurações do projeto',
            'description' => 'Edita dados base, cor e etiquetas do projeto.',
            'tips' => [
                [
                    'title' => 'Cor no calendário',
                    'body' => 'Escolhe uma cor para distinguir o projeto no calendário.',
                ],
                [
                    'title' => 'Etiquetas',
                    'body' => 'Cria tags para organizar tarefas por tema.',
                ],
            ],
            'links' => [],
        ],
        'admin.projects.show' => [
            'title' => 'Projeto em detalhe (admin)',
            'description' => 'Consulta métricas e toma ações administrativas.',
            'tips' => [
                [
                    'title' => 'Suspender ou reativar',
                    'body' => 'Usa as ações no topo para controlar o estado do projeto.',
                ],
                [
                    'title' => 'Eliminar projeto',
                    'body' => 'A eliminação remove permanentemente o projeto e os dados associados.',
                ],
            ],
            'links' => [
                ['label' => 'Voltar ao painel admin', 'route' => 'admin.dashboard'],
            ],
        ],
    ],
];
