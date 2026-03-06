<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isAdmin = (isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] == 'admin');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ripfire System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Seus estilos customizados (carregados depois do Bootstrap para poder sobrescrever) -->
    <link rel="stylesheet" href="assets/estilo.css">

    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#121212">

    <!-- Ícones do Google -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        .navbar-brand img {
            max-height: 40px;
        }
    </style>
</head>
<body>

<!-- SIDEBAR LATERAL -->
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="assets/img/logo_rip.png" alt="Ripfire" class="logo">
        <h5>Ripfire</h5>
    </div>

    <nav class="sidebar-menu">
        <a href="index.php?rota=dashboard" class="menu-item">
            <span class="menu-icon">📊</span>
            <span class="menu-text">Dashboard</span>
        </a>

        <!-- DROPDOWN 1: ESTOQUE -->
        <div class="menu-dropdown">
            <button class="menu-item dropdown-btn">
                <span class="menu-icon">📦</span>
                <span class="menu-text">Estoque</span>
                <span class="menu-arrow">▼</span>
            </button>
            <div class="dropdown-content">
                <a href="index.php?rota=estoque_saldo" class="dropdown-item">Saldo Atual</a>
                <a href="index.php?rota=entrada" class="dropdown-item">Movimentar</a>
                <a href="index.php?rota=estoque_historico" class="dropdown-item">Histórico</a>
                <a href="index.php?rota=relatorio_perdas" class="dropdown-item">Perdas / Quebras</a>
            </div>
        </div>

        <!-- DROPDOWN 2: OPERACIONAL -->
        <div class="menu-dropdown">
            <button class="menu-item dropdown-btn">
                <span class="menu-icon">⚙️</span>
                <span class="menu-text">Operacional</span>
                <span class="menu-arrow">▼</span>
            </button>
            <div class="dropdown-content">
                <a href="index.php?rota=novo_gabarito" class="dropdown-item">Nova Ficha Técnica</a>
                <a href="index.php?rota=novo_dtf" class="dropdown-item">Novo Pedido DTF</a>
                <a href="index.php?rota=listar_gabaritos" class="dropdown-item">Ver Fichas</a>
                <hr class="dropdown-divider">
                <a href="index.php?rota=pedidos" class="dropdown-item">Produção (Pedidos)</a>
                <a href="index.php?rota=compras" class="dropdown-item">Compras</a>
                <a href="index.php?rota=servicos" class="dropdown-item">Serviços / OS</a>
            </div>
        </div>

        <!-- DROPDOWN 3: CADASTROS -->
        <div class="menu-dropdown">
            <button class="menu-item dropdown-btn">
                <span class="menu-icon">📝</span>
                <span class="menu-text">Cadastros</span>
                <span class="menu-arrow">▼</span>
            </button>
            <div class="dropdown-content">
                <a href="index.php?rota=produtos" class="dropdown-item">Produtos</a>
                <a href="index.php?rota=clientes" class="dropdown-item">Clientes</a>
                <a href="index.php?rota=fornecedores" class="dropdown-item">Fornecedores</a>
                <a href="index.php?rota=empresas" class="dropdown-item">Minhas Empresas</a>
            </div>
        </div>

        <?php if ($isAdmin): ?>
        <a href="index.php?rota=gerenciar_usuarios" class="menu-item">
            <span class="menu-icon">👥</span>
            <span class="menu-text">Administração</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Visitante') ?></div>
            <div class="user-level"><?= htmlspecialchars($_SESSION['user_nivel'] ?? '') ?></div>
        </div>
        <a href="index.php?rota=logout" class="btn-logout">Sair</a>
    </div>
</aside>

<!-- Abre o container principal do conteúdo da página -->
<main class="main-content">
