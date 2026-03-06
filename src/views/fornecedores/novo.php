<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">
            <?= isset($fornecedor['id']) ? '✏️ Editar Fornecedor' : '✨ Novo Fornecedor' ?>
        </h1>
        <a href="index.php?rota=fornecedores" class="btn-red" style="text-decoration:none;">Cancelar</a>
    </div>

    <div style="background: var(--bg-surface-2); padding: 30px; border-radius: 8px; border: 1px solid #444; max-width:800px; margin:0 auto;">
        
        <form action="index.php?rota=salvar_fornecedor" method="POST">
            
            <?php if(isset($fornecedor['id'])): ?>
                <input type="hidden" name="id" value="<?= $fornecedor['id'] ?>">
            <?php endif; ?>

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; color:#aaa; margin-bottom:5px;">Nome do Fornecedor / Empresa</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($fornecedor['nome'] ?? '') ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                </div>
                <div>
                    <label style="display:block; color:#aaa; margin-bottom:5px;">CNPJ (Opcional)</label>
                    <input type="text" name="cnpj" value="<?= htmlspecialchars($fornecedor['cnpj'] ?? '') ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; color:#aaa; margin-bottom:5px;">Contato (Nome ou Telefone)</label>
                    <input type="text" name="contato" value="<?= htmlspecialchars($fornecedor['contato'] ?? '') ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                </div>
                <div>
                    <label style="display:block; color:#aaa; margin-bottom:5px;">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($fornecedor['email'] ?? '') ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; color:#aaa; margin-bottom:5px;">Categoria de Fornecimento</label>
                <input type="text" name="categoria" placeholder="Ex: Malhas, Aviamentos, Tintas..." value="<?= htmlspecialchars($fornecedor['categoria'] ?? '') ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 2fr 1fr; gap:20px; margin-bottom:20px;">
                <div>
                    <label style="display:block; color:#aaa; margin-bottom:5px;">CEP</label>
                    <input type="text" name="cep" value="<?= htmlspecialchars($fornecedor['cep'] ?? '') ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                </div>
                <div>
                    <label style="display:block; color:#aaa; margin-bottom:5px;">Endereço</label>
                    <input type="text" name="endereco" value="<?= htmlspecialchars($fornecedor['endereco'] ?? '') ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                </div>
                <div>
                    <label style="display:block; color:#aaa; margin-bottom:5px;">Cidade</label>
                    <input type="text" name="cidade" value="<?= htmlspecialchars($fornecedor['cidade'] ?? '') ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                </div>
            </div>

            <button type="submit" class="btn-green" style="width:100%; padding:15px; font-size:16px;">
                💾 Salvar Fornecedor
            </button>
        </form>
    </div>
</div>

</body>
</html>
