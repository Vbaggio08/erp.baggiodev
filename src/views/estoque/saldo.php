<?php include __DIR__ . '/../geral/header.php'; ?>

<style>
    .badge-estoque { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .status-vazio { background: #e74c3c; color: #fff; }
    .status-baixo { background: #f39c12; color: #fff; }
    .status-ok { background: #2ecc71; color: #fff; }
    .linha-zerada { background: rgba(231, 76, 60, 0.05) !important; }
</style>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1 class="login-title" style="margin-bottom:5px;">📦 Saldo de Estoque</h1>
            <p style="color:#888; font-size:13px;">Inventário completo, incluindo itens sem saldo.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="index.php?rota=tela_entrada" class="btn-green" style="text-decoration:none;">+ Nova Movimentação</a>
            <a href="index.php?rota=estoque_historico" class="btn-blue" style="text-decoration:none;">Ver Histórico</a>
        </div>
    </div>

    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444;">
        <table style="width:100%; border-collapse: collapse; color:#ddd; font-size:14px;">
            <thead>
                <tr style="background:#222; text-align:left; border-bottom:2px solid #555;">
                    <th style="padding:12px;">SKU</th>
                    <th style="padding:12px;">Produto / Modelo</th>
                    <th style="padding:12px;">Cor</th>
                    <th style="padding:12px; text-align:center;">Tamanho</th>
                    <th style="padding:12px; text-align:center;">Saldo Atual</th>
                    <th style="padding:12px; text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($estoque)): ?>
                    <?php foreach($estoque as $item): ?>
                        <?php 
                            $saldo = (int)($item['saldo_total'] ?? 0);
                            $classeStatus = 'status-ok';
                            $textoStatus = 'OK';

                            if ($saldo <= 0) {
                                $classeStatus = 'status-vazio';
                                $textoStatus = 'SEM ESTOQUE';
                            } elseif ($saldo <= 5) {
                                $classeStatus = 'status-baixo';
                                $textoStatus = 'BAIXO';
                            }
                        ?>
                        <tr style="border-bottom:1px solid #333;" class="<?= $saldo <= 0 ? 'linha-zerada' : '' ?>">
                            <td style="padding:12px; color:#777;"><?= htmlspecialchars($item['sku'] ?? '-') ?></td>
                            <td style="padding:12px;"><strong style="color:#fff;"><?= htmlspecialchars($item['produto']) ?></strong></td>
                            <td style="padding:12px; color:#aaa;"><?= htmlspecialchars($item['cor'] ?? '-') ?></td>
                            <td style="padding:12px; text-align:center; font-weight:bold;"><?= htmlspecialchars($item['tamanho'] ?? 'UN') ?></td>
                            
                            <td style="padding:12px; text-align:center; font-size:16px; font-weight:bold; color: <?= $saldo <= 0 ? '#e74c3c' : '#2ecc71' ?>;">
                                <?= $saldo ?>
                            </td>

                            <td style="padding:12px; text-align:center;">
                                <span class="badge-estoque <?= $classeStatus ?>"><?= $textoStatus ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="padding:30px; text-align:center;">Nenhum produto encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>