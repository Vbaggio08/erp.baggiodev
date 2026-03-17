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
            max-width: 420px;
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

        .divider {
            margin: 20px 0;
            border-top: 1px solid #333;
            position: relative;
        }

        .divider span {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e1e1e;
            padding: 0 8px;
            color: #999;
            font-size: 11px;
            text-transform: uppercase;
        }

        .btn-ponto {
            width: 100%;
            padding: 12px;
            background-color: #16a085;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 8px;
        }

        .btn-ponto:hover {
            background-color: #1abc9c;
        }

        .retorno-ponto {
            margin-top: 10px;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            display: none;
            text-align: left;
        }

        .retorno-ok {
            background: rgba(46, 204, 113, 0.15);
            border: 1px solid rgba(46, 204, 113, 0.5);
            color: #b8f5d4;
        }

        .retorno-erro {
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.5);
            color: #ffb3ab;
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
                <label>Usuário, Email ou CPF</label>
                <input type="text" name="login" required placeholder="seuusuario, email@dominio.com ou 000.000.000-00">
            </div>
            
            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="senha" required placeholder="••••">
            </div>

            <button type="submit" class="btn-login">ENTRAR</button>
        </form>

        <div class="divider"><span>ou</span></div>

        <div>
            <div class="input-group">
                <label>Bater Ponto por CPF</label>
                <input type="text" id="cpf_ponto" maxlength="14" placeholder="000.000.000-00">
            </div>
            <button type="button" class="btn-ponto" id="btn-bater-ponto-login">BATER PONTO</button>
            <div id="retorno_ponto" class="retorno-ponto"></div>
        </div>

        <!-- Elemento de vídeo oculto para captura de câmera -->
        <video id="video-preview-login" style="display: none;" width="320" height="240" autoplay playsinline></video>

        <div class="footer">
            Sistema de Gestão Interna v2.0
        </div>
    </div>

    <script>
        let streamVideoLogin = null;

        function gerarDeviceIdLogin() {
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText(navigator.userAgent, 2, 2);
                return canvas.toDataURL().slice(-32);
            } catch (e) {
                return 'fallback-' + navigator.userAgent.slice(0, 20);
            }
        }

        function normalizarCpf(valor) {
            return (valor || '').replace(/\D+/g, '');
        }

        function aplicarMascaraCpf(input) {
            const digits = normalizarCpf(input.value).slice(0, 11);
            let out = digits;
            out = out.replace(/(\d{3})(\d)/, '$1.$2');
            out = out.replace(/(\d{3})(\d)/, '$1.$2');
            out = out.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            input.value = out;
        }

        function mostrarRetornoPonto(ok, texto) {
            const box = document.getElementById('retorno_ponto');
            box.style.display = 'block';
            box.className = 'retorno-ponto ' + (ok ? 'retorno-ok' : 'retorno-erro');
            box.textContent = texto;
        }

        async function iniciarCameraLogin() {
            try {
                const videoEl = document.getElementById('video-preview-login');
                if (streamVideoLogin) {
                    videoEl.srcObject = streamVideoLogin;
                    return true;
                }

                streamVideoLogin = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 320 }, height: { ideal: 240 } },
                    audio: false
                });
                videoEl.srcObject = streamVideoLogin;
                return true;
            } catch (e) {
                console.log('Câmera não disponível:', e);
                return false;
            }
        }

        function capturarFotoLogin() {
            const videoEl = document.getElementById('video-preview-login');
            if (!videoEl || !videoEl.srcObject) return null;
            const canvas = document.createElement('canvas');
            canvas.width = 320;
            canvas.height = 240;
            canvas.getContext('2d').drawImage(videoEl, 0, 0, 320, 240);
            return canvas.toDataURL('image/jpeg', 0.6);
        }

        function pararCameraLogin() {
            if (streamVideoLogin) {
                streamVideoLogin.getTracks().forEach(t => t.stop());
                streamVideoLogin = null;
            }
        }

        document.getElementById('cpf_ponto').addEventListener('input', function() {
            aplicarMascaraCpf(this);
        });

        document.getElementById('btn-bater-ponto-login').addEventListener('click', async function() {
            const btn = this;
            const cpfInput = document.getElementById('cpf_ponto');
            const cpf = normalizarCpf(cpfInput.value);

            if (cpf.length !== 11) {
                mostrarRetornoPonto(false, 'Informe um CPF válido com 11 dígitos.');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'PROCESSANDO...';

            try {
                // Tentar iniciar câmera para capturar foto
                await iniciarCameraLogin();
                
                // Dar um tempo para o vídeo renderizar e estabilizar
                await new Promise(resolve => setTimeout(resolve, 800));
                
                let fotoBase64 = null;
                fotoBase64 = capturarFotoLogin();

                const payload = {
                    cpf: cpf,
                    device_id: gerarDeviceIdLogin()
                };

                // Se temos foto, adicionar
                if (fotoBase64) {
                    payload.foto = fotoBase64;
                }

                const resp = await fetch('index.php?rota=bater_ponto_cpf_login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await resp.json();
                if (data && data.sucesso) {
                    const tipo = data.tipo_batida === 'entrada' ? 'Entrada' : 'Saída';
                    const usuario = data.usuario ? (' - ' + data.usuario) : '';
                    const fotoInfo = data.foto_salva ? ' (foto capturada)' : '';
                    mostrarRetornoPonto(true, 'Ponto batido: ' + tipo + usuario + fotoInfo);
                    cpfInput.value = '';
                } else {
                    mostrarRetornoPonto(false, (data && data.erro) ? data.erro : 'Não foi possível bater o ponto.');
                }
            } catch (e) {
                mostrarRetornoPonto(false, 'Falha de conexão ao bater ponto.');
            } finally {
                pararCameraLogin();
                btn.disabled = false;
                btn.textContent = 'BATER PONTO';
            }
        });
    </script>

</body>
</html>