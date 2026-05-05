
<div class="box-relatorio">
    <div class="page-header">
        <h1 class="login-title icon-red"><i class="bi bi-exclamation-triangle action-icon"></i>Relatório de Perdas</h1>
        <a href="index.php?rota=dashboard" class="btn-green text-decoration-none">
            <i class="bi bi-arrow-left action-icon"></i>Voltar
        </a>
    </div>

    <div class="panel mb-4">
        <form method="GET" action="index.php" class="filter-bar">
            <input type="hidden" name="rota" value="relatorio_perdas">
            <input type="text" name="busca" class="form-control" style="flex:1; min-width:260px;"
                   placeholder="Buscar por produto, usuário ou observação..."
                   value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
            <button type="submit" class="btn-green">
                <i class="bi bi-search action-icon"></i>Buscar
            </button>
            <?php if (!empty($_GET['busca'])): ?>
                <a href="index.php?rota=relatorio_perdas" class="btn-red text-decoration-none">
                    <i class="bi bi-x-circle action-icon"></i>Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-shell" style="border-left: 4px solid var(--brand-red);">
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Produto</th>
                    <th>Detalhes</th>
                    <th>Qtd Perdida</th>
                    <th>Motivo</th>
                    <th>Responsável</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($perdas)): ?>
                    <?php foreach($perdas as $p): ?>
                    <tr>
                        <td class="text-muted small"><?= date('d/m/Y', strtotime($p['data_movimento'])) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($p['produto']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($p['tamanho']) ?> — <?= htmlspecialchars($p['cor']) ?></td>
                        <td class="fw-bold icon-red fs-6"><?= $p['quantidade'] ?></td>
                        <td><?= htmlspecialchars($p['observacao']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($p['usuario']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center p-4">Nenhuma perda registrada. Parabéns!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>