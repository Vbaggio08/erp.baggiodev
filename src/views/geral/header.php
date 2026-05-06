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
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Seus estilos customizados -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/estilo.css">

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <meta name="theme-color" content="#1a1b1e">

    <!-- Aplica o tema salvo ANTES do primeiro render (evita flash) -->
    <script>
        (function() {
            var t = localStorage.getItem('erp_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    
    <style>
        .navbar-brand img {
            max-height: 40px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
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
                    
                    // Verificar permissão
                    if (!empty($item['requer']) && in_array('admin', $item['requer']) && $nivel_usuario !== 'admin') {
                        continue;
                    }

                    // Ler ícone do item (nome de classe BI)
                    $icon_html = !empty($item['icon'])
                        ? '<i class="bi bi-' . htmlspecialchars($item['icon']) . ' me-1" aria-hidden="true"></i>'
                        : '';
                    
                    // Se não tem submenu, é um link direto
                    if (empty($item['submenu'])) {
                        $is_active = ($rota_atual === $item['rota']) ? ' active' : '';
                        echo '<li class="nav-item">';
                        echo '<a class="nav-link' . $is_active . '" href="' . BASE_URL . 'index.php?rota=' . $item['rota'] . '">';
                        echo $icon_html . htmlspecialchars($item['label']);
                        echo '</a></li>';
                    } else {
                        // Se tem submenu, cria dropdown
                        $id_dropdown = 'dropdown' . $contador;
                        // Verifica se alguma rota do submenu está ativa
                        $submenu_ativo = false;
                        foreach ($item['submenu'] as $si) {
                            if (!empty($si['rota']) && $rota_atual === $si['rota']) {
                                $submenu_ativo = true;
                                break;
                            }
                        }
                        $active_class = $submenu_ativo ? ' active' : '';
                        echo '<li class="nav-item dropdown">';
                        echo '<a class="nav-link dropdown-toggle' . $active_class . '" href="#" id="' . $id_dropdown . '" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                        echo $icon_html . htmlspecialchars($item['label']);
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
                                $sub_icon = !empty($subitem['icon'])
                                    ? '<i class="bi bi-' . htmlspecialchars($subitem['icon']) . '"></i> '
                                    : '';
                                $sub_active = ($rota_atual === ($subitem['rota'] ?? '')) ? ' active' : '';
                                echo '<li>';
                                echo '<a class="dropdown-item' . $sub_active . '" href="' . BASE_URL . 'index.php?rota=' . $subitem['rota'] . '">';
                                echo $sub_icon . htmlspecialchars($subitem['label']);
                                echo '</a>';
                                echo '</li>';
                            }
                        }
                        
                        echo '</ul></li>';
                    }
                }
                ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <!-- Toggle Dark/Light -->
                <button id="btn-theme-toggle" aria-label="Alternar tema" title="Alternar tema claro/escuro">
                    <i class="bi bi-sun" id="theme-icon"></i>
                </button>

                <div class="text-end">
                    <div class="navbar-user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Visitante') ?></div>
                    <div class="navbar-user-role"><?= htmlspecialchars($_SESSION['user_nivel'] ?? '') ?></div>
                </div>
                <a href="<?= BASE_URL ?>index.php?rota=logout" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Sair
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Script do toggle de tema -->
<script>
(function() {
    var btn = document.getElementById('btn-theme-toggle');
    var icon = document.getElementById('theme-icon');
    var html = document.documentElement;

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        if (icon) {
            icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
        }
    }

    // Sincroniza ícone com o tema atual (já definido pelo script inline no head)
    var current = html.getAttribute('data-theme') || 'dark';
    applyTheme(current);

    if (btn) {
        btn.addEventListener('click', function() {
            var next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem('erp_theme', next);
        });
    }
})();
</script>

<!-- Abre o container principal do conteúdo da página -->
<main class="container-fluid py-4">
