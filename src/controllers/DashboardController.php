<?php
require_once __DIR__ . '/../config/database.php';
// Importa os Models
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Estoque.php';
require_once __DIR__ . '/../models/Gabarito.php';

class DashboardController {

    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. PEDIDOS PENDENTES
        $pendentesLista = [];
        if (class_exists('Pedido') && method_exists('Pedido', 'listarPendentes')) {
            $pendentesLista = Pedido::listarPendentes();
        }
        $totalPendentes = count($pendentesLista);

        // 2. ESTOQUE BAIXO (Nova Lógica)
        $estoqueBaixo = [];
        if (class_exists('Estoque') && method_exists('Estoque', 'listarEstoqueBaixo')) {
            $estoqueBaixo = Estoque::listarEstoqueBaixo();
        }
        
        // 3. TOTAL DE PEÇAS (Nova Lógica)
        $totalPecas = 0;
        if (class_exists('Estoque') && method_exists('Estoque', 'getTotalPecas')) {
            $totalPecas = Estoque::getTotalPecas();
        }

        // 4. PERDAS
        $totalPerdas = 0;
        if (class_exists('Estoque') && method_exists('Estoque', 'getRelatorioPerdas')) {
            $perdasLista = Estoque::getRelatorioPerdas();
            $totalPerdas = count($perdasLista);
        }

        // 5. PRODUZIDOS HOJE
        $produzidosHoje = 0;
        if (class_exists('Gabarito') && method_exists('Gabarito', 'contarProducaoHoje')) {
            $produzidosHoje = Gabarito::contarProducaoHoje();
        }

        // Carrega a view do dashboard, passando as variáveis
        require __DIR__ . '/../views/geral/header.php';
        require __DIR__ . '/../views/geral/dashboard.php';
        require __DIR__ . '/../views/geral/footer.php';
    }
}