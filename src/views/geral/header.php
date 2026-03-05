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
    <meta name="theme-color" content="#121212"> <!-- Cor da barra de navegação escura -->

    <!-- Ícones do Google (opcional, se quiser usar no novo layout) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        /* Pequenos ajustes para o Bootstrap se integrar melhor */
        .navbar-brand img {
            max-height: 40px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?rota=dashboard">
            <img src="assets/img/logo_rip.png" alt="Ripfire">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php?rota=dashboard">Dashboard</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="estoqueDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Estoque
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="estoqueDropdown">
                        <li><a class="dropdown-item" href="index.php?rota=estoque_saldo">Saldo Atual</a></li>
                        <li><a class="dropdown-item" href="index.php?rota=entrada">Movimentar</a></li>
                        <li><a class="dropdown-item" href="index.php?rota=estoque_historico">Histórico</a></li>
                        <li><a class="dropdown-item" href="index.php?rota=relatorio_perdas">Perdas / Quebras</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="operacionalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Operacional
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="operacionalDropdown">
                        <li><a class="dropdown-item" href="index.php?rota=novo_gabarito">Nova Ficha Técnica</a></li>
                        <li><a class="dropdown-item" href="index.php?rota=novo_dtf">Novo Pedido DTF</a></li>
                        <li><a class="dropdown-item" href="index.php?rota=listar_gabaritos">Ver Fichas</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php?rota=pedidos">Produção (Pedidos)</a></li>
                        <li><a class="dropdown-item" href="index.php?rota=compras">Compras</a></li>
                        <li><a class="dropdown-item" href="index.php?rota=servicos">Serviços / OS</a></li>
                    </ul>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="cadastrosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Cadastros
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="cadastrosDropdown">
                        <li><a class="dropdown-item" href="index.php?rota=produtos">Produtos</a></li>
                        <li><a class="dropdown-item" href="index.php?rota=clientes">Clientes</a></li>
                        <li><a class="dropdown-item" href="index.php?rota=fornecedores">Fornecedores</a></li>
                        <li><a class="dropdown-item" href="index.php?rota=empresas">Minhas Empresas</a></li>
                    </ul>
                </li>

                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?rota=gerenciar_usuarios">Administração</a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center text-white">
                <div class="me-3 text-end">
                    <div class="fw-bold"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Visitante') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($_SESSION['user_nivel'] ?? '') ?></div>
                </div>
                <a href="index.php?rota=logout" class="btn btn-outline-danger">Sair</a>
            </div>
        </div>
    </div>
</nav>

<!-- Abre o container principal do conteúdo da página -->
<main class="container-fluid py-4">
