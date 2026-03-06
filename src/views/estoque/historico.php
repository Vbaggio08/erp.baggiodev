
<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">📜 Histórico de Movimentações</h1>
        <a href="index.php?rota=estoque_saldo" class="btn-green" style="text-decoration:none;">Voltar ao Saldo</a>
    </div>

    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444;">
        <table style="width:100%; border-collapse: collapse; color:#ddd;">
            <thead>
                <tr style="background:#222; text-align:left;">
                    <th style="padding:12px;">Data/Hora</th>
                    <th style="padding:12px;">Tipo</th>
                    <th style="padding:12px;">Produto</th>
                    <th style="padding:12px;">Tam</th>
                    <th style="padding:12px;">Cor</th>
                    <th style="padding:12px;">Qtd</th>
                    <th style="padding:12px;">Obs</th>
                    <th style="padding:12px;">Usuário</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($movimentacoes)): ?>
                    <?php foreach($movimentacoes as $mov): ?>
                    <tr style="border-bottom:1px solid #333; font-size:14px;">
                        <td style="padding:12px; color:#aaa;">
                            <?= date('d/m/Y H:i', strtotime($mov['data_movimento'])) ?>
                        </td>
                        <td style="padding:12px;">
                            <?php if($mov['tipo'] == 'entrada'): ?>
                                <span style="background:#2ecc71; color:#000; padding:4px 8px; border-radius:4px; font-weight:bold; font-size:11px;">ENTRADA</span>
                            <?php else: ?>
                                <span style="background:#e74c3c; color:#fff; padding:4px 8px; border-radius:4px; font-weight:bold; font-size:11px;">SAÍDA</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px; font-weight:bold; color:#fff;">
                            <?= htmlspecialchars($mov['produto']) ?>
                        </td>
                        <td style="padding:12px;"><?= htmlspecialchars($mov['tamanho']) ?></td>
                        <td style="padding:12px;"><?= htmlspecialchars($mov['cor']) ?></td>
                        <td style="padding:12px; font-weight:bold; font-size:15px;">
                            <?= $mov['quantidade'] ?>
                        </td>
                        <td style="padding:12px; color:#aaa; font-style:italic;">
                            <?= htmlspecialchars($mov['observacao']) ?>
                        </td>
                        <td style="padding:12px; color:#888;">
                            <?= htmlspecialchars($mov['usuario']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center; padding:30px;">Nenhum histórico encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>