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
            'rota' => 'dashboard',
            'icon' => '📊',
            'requer' => []
        ],

        // 📦 Estoque
        [
            'label' => 'Estoque',
            'icon' => '📦',
            'requer' => [],
            'submenu' => [
                [
                    'label' => 'Saldo Atual',
                    'rota' => 'estoque_saldo',
                    'requer' => []
                ],
                [
                    'label' => 'Movimentar',
                    'rota' => 'entrada',
                    'requer' => []
                ],
                [
                    'label' => 'Histórico',
                    'rota' => 'estoque_historico',
                    'requer' => []
                ],
                [
                    'label' => 'Perdas / Quebras',
                    'rota' => 'relatorio_perdas',
                    'requer' => []
                ]
            ]
        ],

        // ⚙️ Operacional
        [
            'label' => 'Operacional',
            'icon' => '⚙️',
            'requer' => [],
            'submenu' => [
                [
                    'label' => 'Produção de Camiseta',
                    'rota' => 'novo_gabarito',
                    'requer' => []
                ],
                [
                    'label' => 'Produção DTF',
                    'rota' => 'novo_dtf',
                    'requer' => []
                ],
                [
                    'label' => 'Ver Produção de Camiseta',
                    'rota' => 'listar_gabaritos',
                    'requer' => []
                ],
                [
                    'label' => 'Ver Produção DTF',
                    'rota' => 'ver_producao_dtf',
                    'requer' => []
                ],
                [
                    'label' => '---',
                    'divisor' => true
                ],
                [
                    'label' => 'Produção (Pedidos)',
                    'rota' => 'pedidos',
                    'requer' => []
                ],
                [
                    'label' => 'Compras',
                    'rota' => 'compras',
                    'requer' => []
                ],
                [
                    'label' => 'Serviços / OS',
                    'rota' => 'servicos',
                    'requer' => []
                ]
            ]
        ],

        // 📝 Cadastros
        [
            'label' => 'Cadastros',
            'icon' => '📝',
            'requer' => [],
            'submenu' => [
                [
                    'label' => 'Produtos',
                    'rota' => 'produtos',
                    'requer' => []
                ],
                [
                    'label' => 'Clientes',
                    'rota' => 'clientes',
                    'requer' => []
                ],
                [
                    'label' => 'Fornecedores',
                    'rota' => 'fornecedores',
                    'requer' => []
                ],
                [
                    'label' => 'Minhas Empresas',
                    'rota' => 'empresas',
                    'requer' => []
                ]
            ]
        ],

        // 🔐 ADMINISTRAÇÃO - Transformado em dropdown
        [
            'label' => 'Administração',
            'icon' => '🔐',
            'requer' => ['admin'],
            'submenu' => [
                [
                    'label' => 'Gerenciar Usuários',
                    'rota' => 'gerenciar_usuarios',
                    'requer' => ['admin'],
                    'icon' => '👥'
                ]
            ]
        ]
    ]
];
