<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ripfire System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta name="theme-color" content="#f3f5f8">
    <style>
        :root {
            --bg: #f3f5f8;
            --surface: #ffffff;
            --surface-2: #eef2f7;
            --text: #1f2937;
            --text-muted: #66758a;
            --border: #d7dee8;
            --primary: #c98a00;
            --primary-strong: #9f6b00;
            --focus: rgba(201, 138, 0, 0.3);
            --danger-bg: #fff5db;
            --danger-border: #f4ca68;
            --danger-text: #7b5d06;
        }

        body[data-theme="dark"] {
            --bg: #1e232b;
            --surface: #262d37;
            --surface-2: #303845;
            --text: #edf2f7;
            --text-muted: #aeb8c7;
            --border: #3f4a5b;
            --primary: #f0b22f;
            --primary-strong: #ffd36a;
            --focus: rgba(240, 178, 47, 0.35);
            --danger-bg: #4a3f1f;
            --danger-border: #8d7330;
            --danger-text: #ffe8ad;
        }

        body {
            margin: 0;
            padding: 24px;
            background:
                radial-gradient(circle at 10% 20%, rgba(201, 138, 0, 0.08), transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(36, 132, 216, 0.12), transparent 38%),
                var(--bg);
            color: var(--text);
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-wrap {
            width: min(100%, 440px);
        }

        .login-topbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .theme-toggle {
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            border-radius: 999px;
            width: 42px;
            height: 42px;
            cursor: pointer;
        }

        .login-card {
            background-color: var(--surface);
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.12);
            width: 100%;
            text-align: center;
            border: 1px solid var(--border);
        }

        /* --- AJUSTE DA LOGO --- */
        .logo {
            display: flex;            /* Alinha ícone e texto lado a lado */
            flex-direction: column;   /* Coloca o texto abaixo do ícone (estilo marca) */
            align-items: center;      /* Centraliza tudo */
            gap: 10px;                /* Espaço entre ícone e texto */
            margin-bottom: 30px;
        }

        .logo img {
            width: auto;
            max-height: 80px;        /* Define a altura máxima da logo no login */
            display: block;
        }

        .logo-text {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-muted);
        }
        .input-group input {
            width: 100%;
            padding: 12px;
            background-color: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            box-sizing: border-box; 
            font-size: 16px;
        }
        .input-group input::placeholder {
            color: var(--text-muted);
        }
        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem var(--focus);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: #101827;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-login:hover {
            background-color: var(--primary-strong);
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .alert-timeout {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 14px;
            text-align: center;
        }

    </style>
</head>
<body>

    <div class="login-wrap">
        <div class="login-topbar">
            <button id="loginThemeToggle" class="theme-toggle" type="button" aria-label="Alternar tema" title="Alternar tema">
                <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
            </button>
        </div>

        <div class="login-card">
            <div class="logo">
                <img src="assets/img/logo_rip.png" alt="Logo Ripfire">
                <span class="logo-text">Ripfire</span>
            </div>
            
            <?php if (isset($_GET['timeout']) && $_GET['timeout'] == '1'): ?>
            <div class="alert-timeout">
                Sua sessão expirou por inatividade. Faça login novamente.
            </div>
            <?php endif; ?>

            <form action="index.php?rota=autenticar" method="POST">
                <div class="input-group">
                    <label>Usuário, Email ou CPF</label>
                    <input type="text" name="login" required placeholder="seuusuario, email@dominio.com ou 000.000.000-00">
                </div>
                
                <div class="input-group">
                    <label>Senha</label>
                    <input type="password" name="senha" required placeholder="••••">
                </div>

                <button type="submit" class="btn-login">Entrar</button>
            </form>

            <div class="footer">
                Sistema de Gestão Interna v2.0
            </div>
        </div>
    </div>

    <script>
        (function () {
            const STORAGE_KEY = 'erp-theme';
            const body = document.body;
            const metaTheme = document.querySelector('meta[name="theme-color"]');
            const toggle = document.getElementById('loginThemeToggle');

            function applyTheme(theme) {
                body.setAttribute('data-theme', theme);
                if (metaTheme) {
                    metaTheme.setAttribute('content', theme === 'dark' ? '#1e232b' : '#f3f5f8');
                }
                if (toggle) {
                    const icon = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
                    toggle.innerHTML = '<i class="' + icon + '" aria-hidden="true"></i>';
                }
            }

            const saved = localStorage.getItem(STORAGE_KEY);
            applyTheme(saved === 'dark' ? 'dark' : 'light');

            toggle?.addEventListener('click', function () {
                const current = body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                localStorage.setItem(STORAGE_KEY, next);
                applyTheme(next);
            });
        })();
    </script>

</body>
</html>