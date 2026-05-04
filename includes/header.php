<?php
//--------
//ARQUIVO: includes/header.php
//Responsabilidade
//iniciar a estrutua html padrão ( head, navbar e abertura do main)
//todas as paginas incluem este arquivo para reaproveitar layout

//
if(!isset($pageTitle)) {
    $pageTitle = 'CRUD de Usuarios';
}

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
     <!-- Bootstrap CSS -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
     <!-- Fontes -->
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
     <!-- Bootstrap Icons -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
     <!-- Estilos do projeto -->
     <link rel="stylesheet" href="/projetoCrud2/style.css">
</head>
<body>
    <script>
        (function () {
            if (localStorage.getItem('tema') === 'dark') {
                document.body.classList.add('theme-dark');
            }
        })();
    </script>
    <nav class="navbar navbar-expand-lg border-bottom mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-grid-1x2-fill me-2"></i>
                <span>ERP Acadêmico</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array($currentPage, ['index.php', 'form.php', 'editar.php'], true) ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-people me-1"></i>Usuarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array($currentPage, ['notas_index.php', 'notas_form.php'], true) ? 'active' : ''; ?>" href="notas_index.php">
                            <i class="bi bi-journal-check me-1"></i>Notas
                        </a>
                    </li>
                </ul>
                <button
                    id="themeToggle"
                    class="btn btn-sm btn-outline-secondary ms-auto mt-2 mt-lg-0"
                    aria-label="Alternar tema claro/escuro"
                    data-bs-toggle="tooltip"
                    data-bs-placement="bottom"
                    title="Alternar tema"
                >
                    <i id="themeIcon" class="bi bi-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container">

  
    

