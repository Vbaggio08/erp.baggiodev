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

        // 🕐 PONTO - Novo grupo centralizado
        [
            'label' => 'Ponto',
            'icon' => '🕐',
            'requer' => [],
            'submenu' => [
                [
                    'label' => 'Bater Ponto',
                    'rota' => 'bater_ponto',
                    'requer' => [],
                    'icon' => '⏱️'
                ],
                [
                    'label' => 'Meu Ponto',
                    'rota' => 'meu_ponto',
                    'requer' => [],
                    'icon' => '📋'
                ],
                [
                    'label' => 'Solicitar Atestado',
                    'rota' => 'solicitar_atestado',
                    'requer' => [],
                    'icon' => '📄'
                ],
                [
                    'label' => 'Dashboard Ponto',
                    'rota' => 'dashboard_ponto',
                    'requer' => [],
                    'icon' => '📈'
                ],
                [
                    'label' => 'Gerenciar Meu Ponto',
                    'rota' => 'gerenciar_ponto_pessoal',
                    'requer' => [],
                    'icon' => '⚙️'
                ],
                [
                    'label' => '---',
                    'divisor' => true
                ]
            ]
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
                    'label' => 'Nova Ficha Técnica',
                    'rota' => 'novo_gabarito',
                    'requer' => []
                ],
                [
                    'label' => 'Novo Pedido DTF',
                    'rota' => 'novo_dtf',
                    'requer' => []
                ],
                [
                    'label' => 'Ver Fichas',
                    'rota' => 'listar_gabaritos',
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
                ],
                [
                    'label' => 'Gerenciar Pontos',
                    'rota' => 'gerenciar_ponto_todos',
                    'requer' => ['admin'],
                    'icon' => '🕐'
                ],
                [
                    'label' => 'Dashboard RH',
                    'rota' => 'dashboard_rh',
                    'requer' => ['admin'],
                    'icon' => '📊'
                ],
                [
                    'label' => 'Auditoria',
                    'rota' => 'auditoria_dashboard',
                    'requer' => ['admin'],
                    'icon' => '🔍'
                ],
                [
                    'label' => '---',
                    'divisor' => true
                ],
                [
                    'label' => 'Horas Extras',
                    'rota' => 'horas_extras_aprovar',
                    'requer' => ['admin'],
                    'icon' => '⏳'
                ],
                [
                    'label' => 'Configuração de Ponto',
                    'rota' => 'configuracao_ponto',
                    'requer' => ['admin'],
                    'icon' => '⚙️'
                ]
            ]
        ]
    ]
];
