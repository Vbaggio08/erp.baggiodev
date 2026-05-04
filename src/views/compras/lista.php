<style>
    .status-select {
        padding: 6px 10px; border-radius: 15px; border: none; font-weight: bold; cursor: pointer; color: #fff; text-align: center; width: 100%; max-width: 130px; font-size: 12px; appearance: none; -webkit-appearance: none; text-align-last: center; transition: 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .status-Pendente { background-color: #f39c12; }       /* Laranja */
    .status-Aguardando { background-color: #3498db; }     /* Azul */
    .status-Recebido { background-color: #2ecc71; }       /* Verde */
    .status-Cancelado { background-color: #e74c3c; }      /* Vermelho */

    .status-select:hover { opacity: 0.9; transform: scale(1.02); }
</style>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">🛒 Compras / Pedidos de Compra</h1>
        <a href="index.php?rota=nova_compra" class="btn-green" style="text-decoration:none;">+ Nova Compra</a>
    </div>

    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444;">
        <table style="width:100%; border-collapse: collapse; color:#ddd;">
            <thead>
                <tr style="background:#222; text-align:left;">
                    <th style="padding:12px;">ID</th>
                    <th style="padding:12px;">Data</th>
                    <th style="padding:12px;">Fornecedor</th>
                    <th style="padding:12px;">Itens / Resumo</th>
                    <th style="padding:12px;">Total</th>
                    <th style="padding:12px; text-align:center;">Status</th>
                    <th style="padding:12px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($compras)): ?>
                    <?php foreach($compras as $c): ?>
                    
                    <?php $classeStatus = str_replace(' ', '', $c['status'] ?? 'Pendente'); ?>

                    <tr style="border-bottom:1px solid #333;">
                        <td style="padding:12px; color:#aaa;">#<?= $c['id'] ?></td>
                        <td style="padding:12px;"><?= date('d/m/Y', strtotime($c['data_compra'])) ?></td>
                        <td style="padding:12px; font-weight:bold; color:#e6b800;"><?= htmlspecialchars($c['fornecedor'] ?? '-') ?></td>
                        
                        <td style="padding:12px; color:#ccc;">
                            <?= htmlspecialchars(substr($c['produto'], 0, 40)) ?>...
                        </td>
                        
                        <td style="padding:12px; color:#2ecc71; font-weight:bold;">
                            R$ <?= number_format($c['valor_total'] ?? 0, 2, ',', '.') ?>
                        </td>

                        <td style="padding:12px; text-align:center;">
                            <select class="status-select status-<?= $classeStatus ?>" 
                                    onchange="mudarStatusCompra(this, <?= $c['id'] ?>)">
                                <option value="Pendente" <?= $c['status'] == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="Aguardando" <?= $c['status'] == 'Aguardando' ? 'selected' : '' ?>>Aguardando</option>
                                <option value="Recebido" <?= $c['status'] == 'Recebido' ? 'selected' : '' ?>>✅ Recebido</option>
                                <option value="Cancelado" <?= $c['status'] == 'Cancelado' ? 'selected' : '' ?>>❌ Cancelado</option>
                            </select>
                        </td>

                        <td style="padding:12px;">
                             <?php if(isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] === 'admin'): ?>
                                <a href="index.php?rota=compra_excluir&id=<?= $c['id'] ?>" 
                                   onclick="return confirm('Tem certeza?')"
                                   style="color:#e74c3c; text-decoration:none;">
                                    <span class="material-icons">delete</span>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:30px;">Nenhuma compra registrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function mudarStatusCompra(select, id) {
    var novoStatus = select.value;
    select.className = 'status-select status-' + novoStatus;
    window.location.href = 'index.php?rota=compra_mudar_status&id=' + id + '&status=' + novoStatus;
}
</script>