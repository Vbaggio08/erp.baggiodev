<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <div>
            <h1 class="login-title" style="margin:0;">Nova Ordem de Serviço</h1>
            <p class="login-subtitle" style="margin:0;">Registre serviços externos (Facção, Conserto, etc).</p>
        </div>
        <a href="index.php?rota=os_historico" class="login-button btn-red" style="text-decoration:none; padding: 10px 20px;">Cancelar</a>
    </div>

    <form action="index.php?rota=os_salvar" method="POST" 
          style="background: var(--bg-surface-2); padding: 30px; border-radius: 12px; border: 1px solid #444;">
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            
            <div style="grid-column: span 2;">
                <label style="display:block; margin-bottom:5px; color:#e6b800; font-weight:bold;">🏢 Empresa Solicitante</label>
                <select name="empresa" required style="width:100%; padding:12px; background:#222; border:1px solid #e6b800; color:#fff; font-weight:bold;">
                    <?php foreach ($empresas as $emp): ?>
                        <option value="<?= htmlspecialchars($emp['nome']) ?>"><?= htmlspecialchars($emp['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="grid-column: span 2;">
                <label style="display:block; margin-bottom:5px; color:#aaa;">🛠️ Prestador / Fornecedor</label>
                <div style="display:flex; gap: 10px;">
                    <select name="cliente_select" style="flex:1; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                        <option value="">-- Selecione da Lista --</option>
                        <?php foreach ($fornecedores as $f): ?>
                            <option value="<?= htmlspecialchars($f['nome']) ?>"><?= htmlspecialchars($f['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="cliente_input" placeholder="Ou digite outro nome..." 
                           style="flex:1; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                </div>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; color:#aaa;">Valor (R$)</label>
                <input type="number" step="0.01" name="valor" placeholder="0.00" 
                       style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; color:#aaa;">Data</label>
                <input type="text" value="<?= date('d/m/Y') ?>" disabled
                       style="width:100%; padding:12px; background:#333; border:1px solid #555; color:#888; border-radius:4px; cursor: not-allowed;">
            </div>

            <div style="grid-column: span 2;">
                <label style="display:block; margin-bottom:5px; color:#aaa;">Descrição do Serviço</label>
                <textarea name="descricao" rows="4" required placeholder="Descreva o serviço realizado..."
                          style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px; font-family: sans-serif;"></textarea>
            </div>
        </div>

        <button type="submit" class="login-button btn-green" style="margin-top:20px; width:100%; height: 50px; font-size:16px;">
            💾 Salvar O.S.
        </button>
    </form>
</div>
</body>
</html>