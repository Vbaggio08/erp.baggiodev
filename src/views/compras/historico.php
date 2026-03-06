<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1 class="login-title">🛒 Histórico de Compras</h1>
        <a href="index.php?rota=nova_compra" class="btn-green" style="text-decoration:none;">+ Nova Compra</a>
    </div>

    <div style="overflow-x:auto; margin-top:20px;">
        <table style="width:100%; border-collapse: collapse; color:#ddd;">
            <thead>
                <tr style="background:#222; text-align:left;">
                    <th style="padding:15px;">Data</th>
                    <th style="padding:15px;">Fornecedor</th>
                    <th style="padding:15px;">Produto / Qtd</th>
                    <th style="padding:15px;">Valor Total</th>
                    <th style="padding:15px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($compras)): ?>
                    <?php foreach ($compras as $c): ?>
                        <tr style="border-bottom:1px solid #333;">
                            <td style="padding:15px;"><?= date('d/m/Y', strtotime($c['data_compra'])) ?></td>
                            <td style="padding:15px; font-weight:bold; color:#e6b800;"><?= htmlspecialchars($c['fornecedor']) ?></td>
                            <td style="padding:15px;">
                                <?= htmlspecialchars($c['produto']) ?> 
                                <span style="background:#333; padding:2px 6px; font-size:12px; border-radius:4px;">x<?= $c['quantidade'] ?></span>
                            </td>
                            <td style="padding:15px; color:#2ecc71;">R$ <?= number_format($c['valor_total'], 2, ',', '.') ?></td>
                            <td style="padding:15px;"><?= htmlspecialchars($c['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:#666;">Nenhuma compra registrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>