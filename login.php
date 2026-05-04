<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    // Login simples com credenciais fixas
    if ($usuario === 'baggio' && $senha === '123') {
        $_SESSION['logado'] = true;
        header('Location: index.php');
        exit;
    }

    $error = 'Usuario ou senha invalidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/projetoCrud2/style.css">
</head>
<body class="d-flex align-items-center justify-content-center">
    <script>
        (function () {
            if (localStorage.getItem('tema') === 'dark') {
                document.body.classList.add('theme-dark');
            }
        })();
    </script>
    <button
        id="themeToggle"
        class="btn btn-sm btn-outline-secondary position-fixed"
        style="top: 1rem; right: 1rem; z-index: 1050;"
        aria-label="Alternar tema claro/escuro"
    >
        <i id="themeIcon" class="bi bi-moon"></i>
    </button>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-5">
                <div class="card shadow-sm border">
                    <div class="card-header py-3">
                        <h2 class="h5 mb-0"><i class="bi bi-shield-lock me-1"></i>Acesso ao sistema</h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php" class="row g-3">
                            <div class="col-12">
                                <label for="usuario" class="form-label">Usuário</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" required autofocus>
                            </div>
                            <div class="col-12">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>
                            <div class="col-12 d-grid mt-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var btn  = document.getElementById('themeToggle');
            var icon = document.getElementById('themeIcon');

            function applyTheme(dark) {
                if (dark) {
                    document.body.classList.add('theme-dark');
                    icon.className = 'bi bi-sun';
                } else {
                    document.body.classList.remove('theme-dark');
                    icon.className = 'bi bi-moon';
                }
            }

            applyTheme(document.body.classList.contains('theme-dark'));

            btn.addEventListener('click', function () {
                var isDark = document.body.classList.contains('theme-dark');
                applyTheme(!isDark);
                localStorage.setItem('tema', isDark ? 'light' : 'dark');
            });
        });
    </script>
</body>
</html>