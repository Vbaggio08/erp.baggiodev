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
            'icon' => 'bi bi-speedometer2',
            'requer' => []
        ],

        // 📦 Estoque
        [
            'label' => 'Estoque',
            'icon' => 'bi bi-box-seam',
            'requer' => [],
            'submenu' => [
                [
                    'label' => 'Saldo Atual',
                    'rota' => 'estoque_saldo',
                    'icon' => 'bi bi-clipboard2-data',
                    'requer' => []
                ],
                [
                    'label' => 'Movimentar',
                    'rota' => 'entrada',
                    'icon' => 'bi bi-arrow-left-right',
                    'requer' => []
                ],
                [
                    'label' => 'Histórico',
                    'rota' => 'estoque_historico',
                    'icon' => 'bi bi-clock-history',
                    'requer' => []
                ],
                [
                    'label' => 'Perdas / Quebras',
                    'rota' => 'relatorio_perdas',
                    'icon' => 'bi bi-exclamation-triangle',
                    'requer' => []
                ]
            ]
        ],

        // ⚙️ Operacional
        [
            'label' => 'Operacional',
            'icon' => 'bi bi-gear',
            'requer' => [],
            'submenu' => [
                [
                    'label' => 'Produção de Camiseta',
                    'rota' => 'novo_gabarito',
                    'icon' => 'bi bi-file-earmark-plus',
                    'requer' => []
                ],
                [
                    'label' => 'Produção DTF',
                    'rota' => 'novo_dtf',
                    'icon' => 'bi bi-printer',
                    'requer' => []
                ],
                [
                    'label' => 'Ver Produção de Camiseta',
                    'rota' => 'listar_gabaritos',
                    'icon' => 'bi bi-list-ul',
                    'requer' => []
                ],
                [
                    'label' => 'Ver Produção DTF',
                    'rota' => 'ver_producao_dtf',
                    'icon' => 'bi bi-images',
                    'requer' => []
                ],
                [
                    'label' => '---',
                    'divisor' => true
                ],
                [
                    'label' => 'Produção (Pedidos)',
                    'rota' => 'pedidos',
                    'icon' => 'bi bi-card-checklist',
                    'requer' => []
                ],
                [
                    'label' => 'Compras',
                    'rota' => 'compras',
                    'icon' => 'bi bi-cart3',
                    'requer' => []
                ],
                [
                    'label' => 'Serviços / OS',
                    'rota' => 'servicos',
                    'icon' => 'bi bi-tools',
                    'requer' => []
                ]
            ]
        ],

        // 📝 Cadastros
        [
            'label' => 'Cadastros',
            'icon' => 'bi bi-journal-text',
            'requer' => [],
            'submenu' => [
                [
                    'label' => 'Produtos',
                    'rota' => 'produtos',
                    'icon' => 'bi bi-tag',
                    'requer' => []
                ],
                [
                    'label' => 'Clientes',
                    'rota' => 'clientes',
                    'icon' => 'bi bi-people',
                    'requer' => []
                ],
                [
                    'label' => 'Fornecedores',
                    'rota' => 'fornecedores',
                    'icon' => 'bi bi-truck',
                    'requer' => []
                ],
                [
                    'label' => 'Minhas Empresas',
                    'rota' => 'empresas',
                    'icon' => 'bi bi-building',
                    'requer' => []
                ]
            ]
        ],

        // 🔐 ADMINISTRAÇÃO - Transformado em dropdown
        [
            'label' => 'Administração',
            'icon' => 'bi bi-shield-lock',
            'requer' => ['admin'],
            'submenu' => [
                [
                    'label' => 'Gerenciar Usuários',
                    'rota' => 'gerenciar_usuarios',
                    'requer' => ['admin'],
                    'icon' => 'bi bi-person-gear'
                ]
            ]
        ]
    ]
];
