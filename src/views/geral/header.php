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
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo_rip.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Seus estilos customizados -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/estilo.css">

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <meta name="theme-color" content="#f3f5f8">

    <!-- Ícones do Google -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        .navbar-brand img {
            max-height: 40px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php?rota=dashboard">
            <img src="<?= BASE_URL ?>assets/img/logo_rip.png" alt="Ripfire">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php
                // Carregar menu centralizado
                $menu_config = require_once __DIR__ . '/../../config/menu.php';
                $nivel_usuario = $_SESSION['user_nivel'] ?? 'usuario';
                $rota_atual = $_GET['rota'] ?? 'dashboard';
                $contador = 0;

                foreach ($menu_config['itens'] as $item) {
                    $contador++;
                    
                    // Verificar permissão: se requer 'admin' e usuário não é admin, pula
                    if (!empty($item['requer']) && in_array('admin', $item['requer']) && $nivel_usuario !== 'admin') {
                        continue;
                    }
                    
                    // Se não tem submenu, é um link direto
                    if (empty($item['submenu'])) {
                        $icon = !empty($item['icon']) ? '<i class="menu-icon ' . htmlspecialchars($item['icon']) . '"></i>' : '';
                        $isActive = ($rota_atual === ($item['rota'] ?? '')) ? ' active' : '';
                        echo '<li class="nav-item">';
                        echo '<a class="nav-link' . $isActive . '" href="' . BASE_URL . 'index.php?rota=' . $item['rota'] . '">';
                        echo $icon;
                        echo $item['label'];
                        echo '</a></li>';
                    } else {
                        // Se tem submenu, cria dropdown — verifica se algum subitem é a rota atual
                        $id_dropdown = 'dropdown' . $contador;
                        $icon = !empty($item['icon']) ? '<i class="menu-icon ' . htmlspecialchars($item['icon']) . '"></i>' : '';
                        $dropdownActive = '';
                        foreach ($item['submenu'] as $sub) {
                            if (isset($sub['rota']) && $sub['rota'] === $rota_atual) {
                                $dropdownActive = ' active';
                                break;
                            }
                        }
                        echo '<li class="nav-item dropdown">';
                        echo '<a class="nav-link dropdown-toggle' . $dropdownActive . '" href="#" id="' . $id_dropdown . '" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                        echo $icon;
                        echo $item['label'];
                        echo '</a>';
                        echo '<ul class="dropdown-menu" aria-labelledby="' . $id_dropdown . '">';
                        
                        foreach ($item['submenu'] as $subitem) {
                            // Verificar permissão do subitem
                            if (!empty($subitem['requer']) && in_array('admin', $subitem['requer']) && $nivel_usuario !== 'admin') {
                                continue;
                            }
                            
                            if (isset($subitem['divisor']) && $subitem['divisor']) {
                                echo '<li><hr class="dropdown-divider"></li>';
                            } else {
                                $subIcon = !empty($subitem['icon']) ? '<i class="action-icon ' . htmlspecialchars($subitem['icon']) . '"></i>' : '';
                                $subActive = (isset($subitem['rota']) && $subitem['rota'] === $rota_atual) ? ' active' : '';
                                echo '<li>';
                                echo '<a class="dropdown-item' . $subActive . '" href="' . BASE_URL . 'index.php?rota=' . $subitem['rota'] . '">';
                                echo $subIcon;
                                echo $subitem['label'];
                                echo '</a>';
                                echo '</li>';
                            }
                        }
                        
                        echo '</ul></li>';
                    }
                }
                ?>
            </ul>

            <div class="d-flex align-items-center">
                <button id="themeToggle" class="btn-theme-toggle" type="button" aria-label="Alternar tema claro e escuro" title="Alternar tema">
                    <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
                </button>
                <div class="me-3 text-end">
                    <div class="fw-bold"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Visitante') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($_SESSION['user_nivel'] ?? '') ?></div>
                </div>
                <a href="<?= BASE_URL ?>index.php?rota=logout" class="btn btn-outline-danger">
                    <i class="action-icon bi bi-box-arrow-right"></i>Sair
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Abre o container principal do conteúdo da página -->
<main class="container-fluid py-4">
