<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="login-box">
    <h1 class="login-title">Nova Venda</h1>
    <p class="login-subtitle">Preencha os dados para a produção</p>

    <form action="index.php?rota=salvar_venda_fila" method="POST" enctype="multipart/form-data">
        
        <div class="input-group">
            <label>Canal de Venda</label>
            <select name="canal" required>
                <option value="Shopee">Shopee</option>
                <option value="Mercado Livre">Mercado Livre</option>
                <option value="Site Próprio">Site Próprio</option>
                <option value="WhatsApp">WhatsApp / Balcão</option>
            </select>
        </div>

        <div class="input-group">
            <label>Tipo de Peça</label>
            <select name="tipo" required id="selectTipo">
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['nome']) ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="input-group">
            <label>Cor do Produto</label>
            <input type="text" name="cor" list="cores_sugeridas" placeholder="Ex: Preto, Branco, Azul..." required autocomplete="off">
            <datalist id="cores_sugeridas">
                <option value="Preto">
                <option value="Branco">
                <option value="Marinho">
                <option value="Vermelho">
                <option value="Mescla">
                <option value="Off-White">
            </datalist>
        </div>

        <div class="input-group">
            <label>Tamanho</label>
            <select name="tamanho" required>
                <option value="P">P</option>
                <option value="M">M</option>
                <option value="G">G</option>
                <option value="GG">GG</option>
                <option value="XG">XG</option>
                <option value="G1">G1</option>
            </select>
        </div>
        
        <div class="input-group">
            <label>Foto da Estampa / Referência</label>
            <input type="file" name="foto" accept="image/*">
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            
            <a href="index.php?rota=dashboard" class="login-button btn-red" style="text-decoration:none;">
                Cancelar
            </a>

            <button type="submit" class="login-button btn-green">
                Enviar Pedido
            </button>
            
        </div>

    </form>
</div>

</body>
</html>