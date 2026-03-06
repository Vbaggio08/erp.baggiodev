<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1 class="login-title">📦 Nova Movimentação</h1>
        <a href="index.php?rota=estoque_saldo" class="btn-red" style="text-decoration:none;">Voltar</a>
    </div>

    <div style="background: var(--bg-surface-2); padding: 25px; border-radius: 8px; border: 1px solid #444; margin-top: 20px;">
        
        <form action="index.php?rota=salvar_estoque" method="POST">
            
            <input type="hidden" name="produto" id="inputProduto">
            <input type="hidden" name="tamanho" id="inputTamanho">
            <input type="hidden" name="cor" id="inputCor">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <div style="grid-column: span 2;">
                    <label style="display:block; margin-bottom:8px; color:#aaa;">Selecione o Produto:</label>
                    <select id="selectProduto" onchange="atualizarCampos()" required style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px; font-size:16px;">
                        <option value="">-- Escolha um item --</option>
                        <?php if (!empty($listaProdutos)): ?>
                            <?php foreach ($listaProdutos as $prod): ?>
                                <option value="<?= $prod['nome'] ?>|<?= $prod['tamanho'] ?>|<?= $prod['cor'] ?>">
                                    [<?= $prod['sku'] ?? 'S/N' ?>] <?= $prod['nome'] ?> - <?= $prod['cor'] ?> (<?= $prod['tamanho'] ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Nenhum produto cadastrado!</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label style="display:block; margin-bottom:8px; color:#aaa;">Tipo:</label>
                    <select name="tipo" required style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                        <option value="entrada">🟢 Entrada (Compra/Produção)</option>
                        <option value="saida">🔴 Saída (Venda/Uso)</option>
                        <option value="perda" style="color:#ff9f43; font-weight:bold;">⚠️ Perda / Quebra</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; margin-bottom:8px; color:#aaa;">Quantidade:</label>
                    <input type="number" name="quantidade" required min="1" placeholder="Ex: 1" 
                           style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                </div>

                <div style="grid-column: span 2;">
                    <label style="display:block; margin-bottom:8px; color:#aaa;">Observação:</label>
                    <textarea name="observacao" rows="2" placeholder="Ex: Rasgado na costura..."
                              style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;"></textarea>
                </div>

            </div>

            <div style="margin-top: 25px;">
                <button type="submit" class="btn-green" style="width:100%; font-size:16px; padding:15px;">
                    💾 Confirmar Movimentação
                </button>
            </div>

        </form>
    </div>
</div>

<script>
function atualizarCampos() {
    var select = document.getElementById('selectProduto');
    var valor = select.value; 
    if(valor) {
        var partes = valor.split('|');
        document.getElementById('inputProduto').value = partes[0];
        document.getElementById('inputTamanho').value = partes[1];
        document.getElementById('inputCor').value = partes[2];
    }
}
</script>