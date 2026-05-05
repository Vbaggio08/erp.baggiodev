
<div class="box-relatorio">
    <div class="page-header">
        <h1 class="login-title"><i class="bi bi-clock-history action-icon"></i>Histórico de Movimentações</h1>
        <a href="index.php?rota=estoque_saldo" class="btn-green text-decoration-none">
            <i class="bi bi-arrow-left action-icon"></i>Voltar ao Saldo
        </a>
    </div>

    <div class="panel mb-4">
        <form method="GET" action="index.php" class="filter-bar">
            <input type="hidden" name="rota" value="estoque_historico">
            <input type="text" name="busca" class="form-control" style="flex:1; min-width:260px;"
                   placeholder="Buscar por produto, usuário ou observação..."
                   value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
            <button type="submit" class="btn-green">
                <i class="bi bi-search action-icon"></i>Buscar
            </button>
            <?php if (!empty($_GET['busca'])): ?>
                <a href="index.php?rota=estoque_historico" class="btn-red text-decoration-none">
                    <i class="bi bi-x-circle action-icon"></i>Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Tipo</th>
                    <th>Produto</th>
                    <th>Tam</th>
                    <th>Cor</th>
                    <th>Qtd</th>
                    <th>Obs</th>
                    <th>Usuário</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($movimentacoes)): ?>
                    <?php foreach($movimentacoes as $mov): ?>
                    <tr>
                        <td class="text-muted small">
                            <?= date('d/m/Y H:i', strtotime($mov['data_movimento'])) ?>
                        </td>
                        <td>
                            <?php if($mov['tipo'] == 'entrada'): ?>
                                <span class="status-badge status-ok">ENTRADA</span>
                            <?php else: ?>
                                <span class="status-badge status-vazio">SAÍDA</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?= htmlspecialchars($mov['produto']) ?></td>
                        <td><?= htmlspecialchars($mov['tamanho']) ?></td>
                        <td><?= htmlspecialchars($mov['cor']) ?></td>
                        <td class="fw-bold fs-6"><?= $mov['quantidade'] ?></td>
                        <td class="text-muted fst-italic"><?= htmlspecialchars($mov['observacao']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($mov['usuario']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center p-4">Nenhum histórico encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>