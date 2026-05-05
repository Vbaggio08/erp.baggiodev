<style>
    .status-select {
        padding: 6px 10px;
        border-radius: 15px;
        border: none;
        font-weight: bold;
        cursor: pointer;
        color: #fff;
        text-align: center;
        width: 100%;
        max-width: 130px;
        font-size: 12px;
        appearance: none;
        -webkit-appearance: none;
        text-align-last: center;
        transition: 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .status-Pendente { background-color: #f39c12; }
    .status-Aguardando { background-color: #3498db; }
    .status-Recebido { background-color: #2ecc71; color: #000; }
    .status-Cancelado { background-color: #e74c3c; }
    .status-select:hover { opacity: 0.9; transform: scale(1.02); }
</style>

<div class="box-relatorio">
    <div class="page-header">
        <h1 class="login-title"><i class="bi bi-cart3 action-icon"></i>Compras / Pedidos de Compra</h1>
        <a href="index.php?rota=nova_compra" class="btn-green text-decoration-none">
            <i class="bi bi-plus-circle action-icon"></i>Nova Compra
        </a>
    </div>

    <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Fornecedor</th>
                    <th>Itens / Resumo</th>
                    <th>Total</th>
                    <th class="text-center">Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($compras)): ?>
                    <?php foreach($compras as $c): ?>
                    <?php $classeStatus = str_replace(' ', '', $c['status'] ?? 'Pendente'); ?>
                    <tr>
                        <td class="text-muted">#<?= $c['id'] ?></td>
                        <td><?= date('d/m/Y', strtotime($c['data_compra'])) ?></td>
                        <td class="fw-bold" style="color: var(--primary-color);"><?= htmlspecialchars($c['fornecedor'] ?? '-') ?></td>
                        <td class="text-muted"><?= htmlspecialchars(substr($c['produto'], 0, 40)) ?>...</td>
                        <td class="fw-bold" style="color: var(--brand-green);">R$ <?= number_format($c['valor_total'] ?? 0, 2, ',', '.') ?></td>
                        <td class="text-center">
                            <select class="status-select status-<?= $classeStatus ?>"
                                    onchange="mudarStatusCompra(this, <?= $c['id'] ?>)">
                                <option value="Pendente" <?= $c['status'] == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="Aguardando" <?= $c['status'] == 'Aguardando' ? 'selected' : '' ?>>Aguardando</option>
                                <option value="Recebido" <?= $c['status'] == 'Recebido' ? 'selected' : '' ?>>Recebido</option>
                                <option value="Cancelado" <?= $c['status'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </td>
                        <td>
                            <?php if(isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] === 'admin'): ?>
                                <a href="index.php?rota=compra_excluir&id=<?= $c['id'] ?>"
                                   onclick="return confirm('Tem certeza?')"
                                   class="text-decoration-none" style="color: var(--brand-red);">
                                    <i class="bi bi-trash3" style="font-size:18px;"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center p-4">Nenhuma compra registrada.</td></tr>
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