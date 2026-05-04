<?php
    $totalGeral = 0;
    if (!empty($_SESSION['lista_compra'])) {
        foreach ($_SESSION['lista_compra'] as $it) {
            $totalGeral += $it['total'] ?? 0;
        }
    }
?>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">🛒 Nova Compra / Pedido</h1>
        <a href="index.php?rota=compras" class="btn-red" style="text-decoration:none;">Cancelar</a>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        
        <div>
            <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444; margin-bottom: 20px;">
                <h3 style="color:#e6b800; margin-top:0; font-size:16px; border-bottom:1px solid #444; padding-bottom:10px;">1. Dados da Compra</h3>
                
                <form action="index.php?rota=salvar_compra" method="POST" id="formSalvar">
                    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:15px; margin-bottom:15px;">
                        <div>
                            <label style="display:block; color:#aaa; font-size:12px; margin-bottom:5px;">Fornecedor</label>
                            <select name="fornecedor" required style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                                <option value="">-- Selecione --</option>
                                <?php foreach($fornecedores as $f): ?>
                                    <option value="<?= $f['nome'] ?>"><?= $f['nome'] ?></option>
                                <?php endforeach; ?>
                                <option value="Avulso">Fornecedor Avulso</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; color:#aaa; font-size:12px; margin-bottom:5px;">Data Compra</label>
                            <input type="date" name="data_compra" value="<?= date('Y-m-d') ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                        </div>
                    </div>
                    <div>
                        <label style="display:block; color:#aaa; font-size:12px; margin-bottom:5px;">Observações</label>
                        <input type="text" name="obs" placeholder="Ex: Reposição de estoque camiseta preta" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                </form>
            </div>

            <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444;">
                <h3 style="color:#e6b800; margin-top:0; font-size:16px; border-bottom:1px solid #444; padding-bottom:10px;">2. Adicionar Produtos</h3>
                
                <form action="index.php?rota=compra_adicionar" method="POST">
                    <div style="display:flex; gap:10px; align-items:flex-end;">
                        <div style="flex:3;">
                            <label style="display:block; color:#aaa; font-size:12px; margin-bottom:5px;">Nome do Produto / Material</label>
                            <input type="text" name="produto" placeholder="Ex: Tinta DTF Branca 1L" required 
                                   style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; color:#aaa; font-size:12px; margin-bottom:5px;">Qtd</label>
                            <input type="number" name="qtd" value="1" step="0.01" required 
                                   style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff; text-align:center;">
                        </div>
                        <div style="flex:1;">
                            <label style="display:block; color:#aaa; font-size:12px; margin-bottom:5px;">Custo (R$)</label>
                            <input type="text" name="valor" placeholder="0,00" required 
                                   style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                        </div>
                        <button type="submit" class="btn-green" style="padding:10px 20px; height:38px;">+ Add</button>
                    </div>
                </form>
            </div>
        </div>

        <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444; height:fit-content;">
            <h3 style="color:#e6b800; margin-top:0; font-size:16px; border-bottom:1px solid #444; padding-bottom:10px;">3. Lista de Compra</h3>

            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                <?php if (empty($_SESSION['lista_compra'])): ?>
                    <p style="color:#666; text-align:center; padding:20px;">Carrinho vazio.</p>
                <?php else: ?>
                    <table style="width:100%; border-collapse: collapse; font-size:13px;">
                        <?php foreach ($_SESSION['lista_compra'] as $idx => $item): ?>
                        <tr style="border-bottom:1px solid #333;">
                            <td style="padding:8px 0;">
                                <strong style="display:block; color:#ddd;"><?= htmlspecialchars($item['produto']) ?></strong>
                                <span style="color:#888;">
                                    <?= $item['qtd'] ?> x R$ <?= number_format($item['valor'] ?? 0, 2, ',', '.') ?>
                                </span>
                            </td>
                            <td style="text-align:right; padding:8px 0; color:#2ecc71;">
                                R$ <?= number_format($item['total'] ?? 0, 2, ',', '.') ?>
                            </td>
                            <td style="width:20px; text-align:right;">
                                <form action="index.php?rota=compra_remover" method="POST">
                                    <input type="hidden" name="index" value="<?= $idx ?>">
                                    <button style="background:none; border:none; color:#e74c3c; cursor:pointer;">X</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>

            <div style="background:#222; padding:15px; border-radius:4px; margin-bottom:15px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:#aaa;">TOTAL:</span>
                    <span style="color:#e6b800; font-size:20px; font-weight:bold;">R$ <?= number_format($totalGeral, 2, ',', '.') ?></span>
                </div>
            </div>

            <button type="button" onclick="document.getElementById('formSalvar').submit()" 
                    class="btn-green" style="width:100%; padding:15px; font-size:16px;">
                ✅ Finalizar Pedido
            </button>
            
            <a href="index.php?rota=compra_limpar" style="display:block; text-align:center; color:#e74c3c; margin-top:15px; text-decoration:none; font-size:12px;">Limpar Carrinho</a>
        </div>
    </div>
</div>