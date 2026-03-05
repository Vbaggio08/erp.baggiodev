<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Verifica nível para opções futuras (se precisar esconder algo)
$isAdmin = (isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] == 'admin');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ripfire System</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/estilo.css">
    <link rel="stylesheet" href="assets/responsivo.css">

    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#4e73df">

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</head>
<body>

<button class="menu-toggle" onclick="toggleSidebar()">
    <span class="material-icons">menu</span>
</button>

<div class="sidebar">
    <div class="logo">
        <img src="assets/img/logo_rip.png" alt="Ripfire">
    </div>

    <div class="menu-links">
        
        <div class="menu-group-title" style="border:none;">Principal</div>
        <a href="index.php?rota=dashboard">
            <span class="material-icons">dashboard</span> Dashboard
        </a>
        
        <div class="menu-group-title">Estoque</div>
        <a href="index.php?rota=estoque_saldo">
            <span class="material-icons">inventory_2</span> Saldo Atual
        </a>
        <a href="index.php?rota=entrada">
            <span class="material-icons">add_circle</span> Movimentar
        </a>
        <a href="index.php?rota=estoque_historico">
            <span class="material-icons">history</span> Histórico
        </a>
        <a href="index.php?rota=relatorio_perdas">
            <span class="material-icons">warning</span> Perdas / Quebras
        </a>

        <div class="menu-group-title">Operacional</div>
        
        <a href="index.php?rota=novo_gabarito" style="color: #e6b800;">
            <span class="material-icons">description</span> Nova Ficha Técnica
        </a>
        <a href="index.php?rota=novo_dtf" style="color: #3498db;">
            <span class="material-icons">layers</span> Novo Pedido DTF
        </a>
        <a href="index.php?rota=listar_gabaritos">
            <span class="material-icons">folder_open</span> Ver Fichas
        </a>
        
        <a href="index.php?rota=pedidos">
            <span class="material-icons">precision_manufacturing</span> Produção (Pedidos)
        </a>
        
        <a href="index.php?rota=compras">
            <span class="material-icons">shopping_cart</span> Compras
        </a>
        
        <a href="index.php?rota=servicos">
            <span class="material-icons">build</span> Serviços / OS
        </a>
        <div class="menu-group-title">Cadastros</div>
        
        <a href="index.php?rota=produtos" style="color:#e6b800;">
            <span class="material-icons">checkroom</span> Produtos
        </a>

        <a href="index.php?rota=clientes">
            <span class="material-icons">people</span> Clientes
        </a>
        <a href="index.php?rota=fornecedores">
            <span class="material-icons">local_shipping</span> Fornecedores
        </a>
        <a href="index.php?rota=empresas">
            <span class="material-icons">business</span> Minhas Empresas
        </a>

        <div class="menu-group-title">Administração</div>
        <a href="index.php?rota=gerenciar_usuarios">
            <span class="material-icons">admin_panel_settings</span> Usuários
        </a>

    </div>

    <div class="user-panel">
        <span class="user-name"><?= $_SESSION['user_name'] ?? 'Visitante' ?></span>
        <span class="user-role"><?= $_SESSION['user_nivel'] ?? '' ?></span>
        <a href="index.php?rota=logout" class="btn-sair">Sair</a>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const body = document.querySelector('body');
    sidebar.classList.toggle('sidebar-visible');
    body.classList.toggle('sidebar-visible');
}

// Opcional: Fechar sidebar ao clicar no conteúdo principal
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    // Se a sidebar está visível, e o clique NÃO foi no menu-toggle nem dentro da sidebar
    if (sidebar.classList.contains('sidebar-visible') && !menuToggle.contains(event.target) && !sidebar.contains(event.target)) {
        toggleSidebar();
    }
});
</script>
</body>