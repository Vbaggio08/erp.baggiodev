<?php
/**
 * Configuração centralizada do menu de navegação
 * Defina estrutura, ícones, rotas e permissões aqui
 * 
 * Todos os arquivos que incluem header.php usarão este menu automaticamente
 * Altere apenas este arquivo para atualizar o menu em todo o sistema!
 * 
 * @return array Estrutura do menu
 */

return [
    'itens' => [
        // Dashboard
        [
            'label' => 'Dashboard',
            'rota'  => 'dashboard',
            'icon'  => 'speedometer2',
            'requer' => []
        ],

        // Estoque
        [
            'label' => 'Estoque',
            'icon'  => 'boxes',
            'requer' => [],
            'submenu' => [
                [
                    'label' => 'Saldo Atual',
                    'rota'  => 'estoque_saldo',
                    'icon'  => 'bar-chart-line',
                    'requer' => []
                ],
                [
                    'label' => 'Movimentar',
                    'rota'  => 'entrada',
                    'icon'  => 'arrow-left-right',
                    'requer' => []
                ],
                [
                    'label' => 'Histórico',
                    'rota'  => 'estoque_historico',
                    'icon'  => 'clock-history',
                    'requer' => []
                ],
                [
                    'label' => 'Perdas / Quebras',
                    'rota'  => 'relatorio_perdas',
                    'icon'  => 'exclamation-triangle',
                    'requer' => []
                ]
            ]
        ],

        // Operacional
        [
            'label' => 'Operacional',
            'icon'  => 'gear-fill',
            'requer' => [],
            'submenu' => [
                [
                    'label' => 'Produção de Camiseta',
                    'rota'  => 'novo_gabarito',
                    'icon'  => 'file-earmark-plus',
                    'requer' => []
                ],
                [
                    'label' => 'Produção DTF',
                    'rota'  => 'novo_dtf',
                    'icon'  => 'printer',
                    'requer' => []
                ],
                [
                    'label' => 'Ver Produção de Camiseta',
                    'rota'  => 'listar_gabaritos',
                    'icon'  => 'list-ul',
                    'requer' => []
                ],
                [
                    'label' => 'Ver Produção DTF',
                    'rota'  => 'ver_producao_dtf',
                    'icon'  => 'collection',
                    'requer' => []
                ],
                [
                    'label' => '---',
                    'divisor' => true
                ],
                [
                    'label' => 'Produção (Pedidos)',
                    'rota'  => 'pedidos',
                    'icon'  => 'clipboard2-check',
                    'requer' => []
                ],
                [
                    'label' => 'Compras',
                    'rota'  => 'compras',
                    'icon'  => 'cart3',
                    'requer' => []
                ],
                [
                    'label' => 'Serviços / OS',
                    'rota'  => 'servicos',
                    'icon'  => 'tools',
                    'requer' => []
                ]
            ]
        ],

        // Cadastros
        [
            'label' => 'Cadastros',
            'icon'  => 'card-list',
            'requer' => [],
            'submenu' => [
                [
                    'label' => 'Produtos',
                    'rota'  => 'produtos',
                    'icon'  => 'tag',
                    'requer' => []
                ],
                [
                    'label' => 'Clientes',
                    'rota'  => 'clientes',
                    'icon'  => 'people',
                    'requer' => []
                ],
                [
                    'label' => 'Fornecedores',
                    'rota'  => 'fornecedores',
                    'icon'  => 'truck',
                    'requer' => []
                ],
                [
                    'label' => 'Minhas Empresas',
                    'rota'  => 'empresas',
                    'icon'  => 'building',
                    'requer' => []
                ]
            ]
        ],

        // Administração
        [
            'label' => 'Administração',
            'icon'  => 'shield-lock-fill',
            'requer' => ['admin'],
            'submenu' => [
                [
                    'label' => 'Gerenciar Usuários',
                    'rota'  => 'gerenciar_usuarios',
                    'icon'  => 'person-gear',
                    'requer' => ['admin']
                ]
            ]
        ]
    ]
];
