<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="login-box">
    <h1 class="login-title">Importar Estoque (CSV)</h1>
    <p class="login-subtitle">Entrada de mercadoria em massa</p>

    <form action="index.php?rota=processar_entrada_csv" method="POST" enctype="multipart/form-data">
        
        <div class="input-group">
            <label>Selecione o arquivo CSV</label>
            <input type="file" name="arquivo_csv" accept=".csv" required style="padding: 10px; height: auto;">
        </div>

        <div style="background: rgba(129, 201, 149, 0.1); border-left: 4px solid var(--brand-green); padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <p style="color: var(--text-high); font-size: 0.9rem; margin:0;">
                <strong>Como funciona:</strong><br>
                O sistema tentará ler o <em>Nome do Produto</em> para descobrir a <strong>Cor</strong> e o <strong>Tamanho</strong> automaticamente.<br><br>
                Compatível com: <strong>UpSeller, Shopee</strong> ou planilhas simples (Nome, Qtd).
            </p>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <a href="index.php?rota=estoque_saldo" class="login-button btn-red" style="text-decoration:none;">
                Cancelar
            </a>
            <button type="submit" class="login-button btn-green">
                Processar Entrada
            </button>
        </div>

    </form>
</div>
</body>
</html>