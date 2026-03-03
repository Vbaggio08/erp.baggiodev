<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title" style="color:#e74c3c;">⚠️ Relatório de Perdas</h1>
        <a href="index.php?rota=dashboard" class="btn-green" style="text-decoration:none;">Voltar</a>
    </div>

    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444; border-left: 5px solid #e74c3c;">
        <table style="width:100%; border-collapse: collapse; color:#ddd;">
            <thead>
                <tr style="background:#222; text-align:left;">
                    <th style="padding:12px;">Data</th>
                    <th style="padding:12px;">Produto</th>
                    <th style="padding:12px;">Detalhes</th>
                    <th style="padding:12px;">Qtd Perdida</th>
                    <th style="padding:12px;">Motivo</th>
                    <th style="padding:12px;">Responsável</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($perdas)): ?>
                    <?php foreach($perdas as $p): ?>
                    <tr style="border-bottom:1px solid #333;">
                        <td style="padding:12px; color:#aaa;">
                            <?= date('d/m/Y', strtotime($p['data_movimento'])) ?>
                        </td>
                        <td style="padding:12px; font-weight:bold; color:#fff;">
                            <?= htmlspecialchars($p['produto']) ?>
                        </td>
                        <td style="padding:12px;">
                            <?= htmlspecialchars($p['tamanho']) ?> - <?= htmlspecialchars($p['cor']) ?>
                        </td>
                        <td style="padding:12px; font-weight:bold; color:#e74c3c;">
                            <?= $p['quantidade'] ?>
                        </td>
                        <td style="padding:12px; color:#fff;">
                            <?= htmlspecialchars($p['observacao']) ?>
                        </td>
                        <td style="padding:12px; color:#888;">
                            <?= htmlspecialchars($p['usuario']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:30px;">Nenhuma perda registrada. Parabéns!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>