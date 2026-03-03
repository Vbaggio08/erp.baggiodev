<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ripfire System</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-card {
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 350px;
            text-align: center;
            border: 1px solid #333;
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
            color: #e6b800;          /* Amarelo Ripfire */
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
            color: #aaa;
        }
        .input-group input {
            width: 100%;
            padding: 12px;
            background-color: #2c2c2c;
            border: 1px solid #444;
            border-radius: 5px;
            color: #fff;
            box-sizing: border-box; 
            font-size: 16px;
        }
        .input-group input:focus {
            outline: none;
            border-color: #e6b800;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #e6b800;
            color: #121212;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-login:hover {
            background-color: #ffcc00;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo">
            <img src="assets/img/logo_rip.png" alt="Logo Ripfire">
            <span class="logo-text">Ripfire</span>
        </div>
        
        <form action="index.php?rota=autenticar" method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="admin@ripfire.com">
            </div>
            
            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="senha" required placeholder="••••">
            </div>

            <button type="submit" class="btn-login">ENTRAR</button>
        </form>

        <div class="footer">
            Sistema de Gestão Interna v2.0
        </div>
    </div>

</body>
</html>