<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="box-relatorio">
    <h1 class="login-title">Minhas Empresas / Filiais</h1>
    
    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444; margin-bottom: 30px;">
        <h3 style="margin-top:0; color:#e6b800;">
            <?= isset($empresaEdit) ? '✏️ Editar Empresa' : '🏢 Nova Empresa' ?>
        </h3>
        
        <form action="index.php?rota=salvar_empresa" method="POST">
            <input type="hidden" name="id" value="<?= $empresaEdit['id'] ?? '' ?>">
            
            <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display:block; color:#aaa; font-size:12px;">Nome Fantasia</label>
                    <input type="text" name="nome" required value="<?= $empresaEdit['nome'] ?? '' ?>" 
                           style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; color:#aaa; font-size:12px;">CNPJ</label>
                    <input type="text" name="cnpj" value="<?= $empresaEdit['cnpj'] ?? '' ?>" 
                           style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; color:#aaa; font-size:12px;">Email</label>
                    <input type="email" name="email" value="<?= $empresaEdit['email'] ?? '' ?>" 
                           style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 2fr 1fr; gap: 15px;">
                <div>
                    <label style="display:block; color:#aaa; font-size:12px;">CEP</label>
                    <input type="text" name="cep" value="<?= $empresaEdit['cep'] ?? '' ?>" placeholder="00000-000"
                           style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; color:#aaa; font-size:12px;">Endereço Completo</label>
                    <input type="text" name="endereco" value="<?= $empresaEdit['endereco'] ?? '' ?>" placeholder="Rua, Número, Bairro"
                           style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                </div>
                <div>
                    <label style="display:block; color:#aaa; font-size:12px;">Cidade/UF</label>
                    <input type="text" name="cidade" value="<?= $empresaEdit['cidade'] ?? '' ?>" 
                           style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                </div>
            </div>

            <div style="margin-top: 20px; display:flex; gap:10px;">
                <button type="submit" class="login-button btn-green" style="width:150px;">
                    <?= isset($empresaEdit) ? 'Atualizar Dados' : 'Cadastrar' ?>
                </button>
                
                <?php if(isset($empresaEdit)): ?>
                    <a href="index.php?rota=empresas" class="login-button btn-red" style="text-decoration:none; text-align:center; padding-top:12px;">Cancelar Edição</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <table style="width:100%; border-collapse: collapse; color:#ddd;">
        <thead>
            <tr style="background:#222; text-align:left;">
                <th style="padding:10px;">Empresa</th>
                <th style="padding:10px;">CNPJ / Email</th>
                <th style="padding:10px;">Cidade</th>
                <th style="padding:10px; text-align:right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($empresas)): ?>
                <?php foreach ($empresas as $emp): ?>
                <tr style="border-bottom:1px solid #333;">
                    <td style="padding:10px; font-weight:bold; color:#e6b800;"><?= htmlspecialchars($emp['nome']) ?></td>
                    <td style="padding:10px;">
                        <?= htmlspecialchars($emp['cnpj']) ?><br>
                        <small style="color:#aaa;"><?= htmlspecialchars($emp['email'] ?? '') ?></small>
                    </td>
                    <td style="padding:10px;"><?= htmlspecialchars($emp['cidade'] ?? '') ?></td>
                    <td style="padding:10px; text-align:right;">
                        <a href="index.php?rota=empresas&id=<?= $emp['id'] ?>" title="Editar" style="text-decoration:none; font-size:18px; margin-right:10px;">✏️</a>
                        
                        <a href="index.php?rota=excluir_empresa&id=<?= $emp['id'] ?>" title="Excluir" 
                           onclick="return confirm('Tem certeza que deseja excluir esta empresa?')" 
                           style="text-decoration:none; font-size:18px;">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center; padding:20px;">Nenhuma empresa cadastrada.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>