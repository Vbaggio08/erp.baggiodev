<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="login-box">
    <h1 class="login-title">Importar Pedidos (CSV)</h1>
    <p class="login-subtitle">Adicione itens à Fila de Produção em massa</p>

    <form action="index.php?rota=processar_importacao" method="POST" enctype="multipart/form-data">
        
        <div class="input-group">
            <label>Selecione o arquivo CSV</label>
            <input type="file" name="arquivo_csv" accept=".csv" required style="padding: 10px; height: auto;">
        </div>

        <div style="background: rgba(138, 180, 248, 0.1); border-left: 4px solid var(--brand-blue); padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <p style="color: var(--text-high); font-size: 0.9rem; margin:0;">
                <strong>Instruções:</strong><br>
                1. O sistema aceita planilhas do <strong>UpSeller / Shopee</strong> (automático).<br>
                2. Ou planilhas simples com colunas: <em>Nome do Produto, Quantidade</em>.<br>
                3. O sistema tentará identificar a <strong>Cor</strong> e o <strong>Tamanho</strong> pelo nome do produto.
            </p>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <a href="index.php?rota=fila_producao" class="login-button btn-red" style="text-decoration:none;">
                Cancelar
            </a>
            <button type="submit" class="login-button btn-green">
                Processar Arquivo
            </button>
        </div>

    </form>
</div>

</body>
</html>