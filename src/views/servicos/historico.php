

<div class="main-content">
    <div class="header-actions">
        <div>
            <h1 class="login-title">Histórico de O.S.</h1>
            <p class="login-subtitle">Serviços emitidos</p>
        </div>
        <a href="index.php?rota=nova_os" class="btn btn-green">+ Nova O.S.</a>
    </div>

    <div class="table-container">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Nº O.S.</th>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Itens</th>
                    <th>Emissor</th>
                    <th class="text-right">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ordens)): ?>
                    <tr><td colspan="6" class="text-center text-muted p-30">Nenhuma O.S. encontrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($ordens as $o): ?>
                    <tr>
                        <td data-label="Nº O.S." class="font-bold" style="color:var(--brand-purple);">#<?= $o['id'] ?></td>
                        <td data-label="Data"><?= date('d/m/Y H:i', strtotime($o['data_emissao'])) ?></td>
                        <td data-label="Cliente" class="font-bold"><?= htmlspecialchars($o['cliente']) ?></td>
                        <td data-label="Itens"><?= $o['qtd_itens'] ?></td>
                        <td data-label="Emissor"><?= htmlspecialchars($o['usuario_emissor']) ?></td>
                        <td data-label="Ação" class="text-right">
                            <a href="index.php?rota=os_ver&id=<?= $o['id'] ?>" class="btn btn-gold btn-sm">
                                🖨️ Ver
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>