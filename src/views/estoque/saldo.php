<style>
    .linha-zerada {
        background: color-mix(in srgb, var(--brand-red) 10%, transparent) !important;
    }

    .search-input-stock {
        flex: 1;
        min-width: 260px;
    }

    .text-success-strong {
        color: var(--brand-green);
    }

    .text-danger-strong {
        color: var(--brand-red);
    }
</style>

<div class="box-relatorio">
    <div class="page-header">
        <div>
            <h1 class="login-title"><i class="bi bi-box-seam action-icon"></i>Saldo de Estoque</h1>
            <p class="page-subtitle">Inventário completo, incluindo itens sem saldo.</p>
        </div>
        <div class="toolbar-actions">
            <a href="index.php?rota=entrada" class="btn-green text-decoration-none">
                <i class="bi bi-arrow-left-right action-icon"></i>Nova Movimentação
            </a>
            <a href="index.php?rota=estoque_historico" class="btn-blue text-decoration-none">
                <i class="bi bi-clock-history action-icon"></i>Ver Histórico
            </a>
        </div>
    </div>

    <div class="panel" style="margin-bottom: 20px;">
        <form method="GET" action="index.php" class="filter-bar">
            <input type="hidden" name="rota" value="estoque_saldo">
            <input type="text" name="busca" class="form-control search-input-stock" placeholder="Buscar por SKU, produto ou cor..." 
                   value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" 
                   >
            <button type="submit" class="btn-green">
                <i class="bi bi-search action-icon"></i>Buscar
            </button>
            <?php if (!empty($_GET['busca'])): ?>
                <a href="index.php?rota=estoque_saldo" class="btn-red text-decoration-none">
                    <i class="bi bi-x-circle action-icon"></i>Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Produto / Modelo</th>
                    <th>Cor</th>
                    <th class="text-center">Tamanho</th>
                    <th class="text-center">Saldo Atual</th>
                    <th class="text-center">Status</th>
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
                        <tr class="<?= $saldo <= 0 ? 'linha-zerada' : '' ?>">
                            <td class="text-muted"><?= htmlspecialchars($item['sku'] ?? '-') ?></td>
                            <td><strong><?= htmlspecialchars($item['produto']) ?></strong></td>
                            <td class="text-muted"><?= htmlspecialchars($item['cor'] ?? '-') ?></td>
                            <td class="text-center fw-bold"><?= htmlspecialchars($item['tamanho'] ?? 'UN') ?></td>
                            
                            <td class="text-center fs-6 fw-bold <?= $saldo <= 0 ? 'text-danger-strong' : 'text-success-strong' ?>">
                                <?= $saldo ?>
                            </td>

                            <td class="text-center">
                                <span class="status-badge <?= $classeStatus ?>"><?= $textoStatus ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center p-4">Nenhum produto encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>