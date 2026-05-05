
<div class="box-relatorio">
    <div class="page-header">
        <h1 class="login-title"><i class="bi bi-clock-history action-icon"></i>Histórico de Compras</h1>
        <a href="index.php?rota=nova_compra" class="btn-green text-decoration-none">
            <i class="bi bi-plus-circle action-icon"></i>Nova Compra
        </a>
    </div>

    <div class="table-shell mt-3">
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Fornecedor</th>
                    <th>Produto / Qtd</th>
                    <th>Valor Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($compras)): ?>
                    <?php foreach ($compras as $c): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($c['data_compra'])) ?></td>
                            <td class="fw-bold" style="color: var(--primary-color);"><?= htmlspecialchars($c['fornecedor']) ?></td>
                            <td>
                                <?= htmlspecialchars($c['produto']) ?>
                                <span class="status-badge status-baixo ms-1">x<?= $c['quantidade'] ?></span>
                            </td>
                            <td class="fw-bold" style="color: var(--brand-green);">R$ <?= number_format($c['valor_total'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($c['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center p-4 text-muted">Nenhuma compra registrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>