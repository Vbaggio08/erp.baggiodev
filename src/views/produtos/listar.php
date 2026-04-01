

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">👕 Cadastro de Produtos</h1>
        <a href="index.php?rota=novo_produto" class="btn-green" style="text-decoration:none;">+ Novo Produto</a>
    </div>

    <div style="background: var(--bg-surface-2); padding: 15px; border-radius: 8px; border: 1px solid #444; margin-bottom: 15px;">
        <form action="index.php" method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <input type="hidden" name="rota" value="produtos">
            <input type="text" name="filtro" value="<?= htmlspecialchars($filtro ?? '') ?>" placeholder="Buscar por SKU, nome, tamanho ou cor" style="flex:1; min-width:240px; padding:10px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
            <button type="submit" class="btn-blue" style="padding:10px 16px;">Filtrar</button>
            <?php if (!empty($filtro)): ?>
                <a href="index.php?rota=produtos" class="btn" style="padding:10px 16px; background:#444; color:#fff; text-decoration:none; border-radius:4px;">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444;">
        <table style="width:100%; border-collapse: collapse; color:#ddd;">
            <thead>
                <tr style="background:#222; text-align:left;">
                    <th style="padding:10px; width:60px;">#</th>
                    <th style="padding:10px;">SKU</th>
                    <th style="padding:10px;">Nome</th>
                    <th style="padding:10px;">Tam</th>
                    <th style="padding:10px;">Cor</th>
                    <th style="padding:10px;">Preço Venda</th>
                    <th style="padding:10px;">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($produtos)): ?>
                    <?php $numeroLinha = (int)($offset ?? 0) + 1; ?>
                    <?php foreach($produtos as $prod): ?>
                    <tr style="border-bottom:1px solid #333;">
                        <td style="padding:10px; color:#888;"><?= $numeroLinha ?></td>
                        <td style="padding:10px; color:#aaa; font-size:12px;"><?= htmlspecialchars($prod['sku']) ?></td>
                        <td style="padding:10px; font-weight:bold;"><?= htmlspecialchars($prod['nome']) ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars($prod['tamanho']) ?></td>
                        <td style="padding:10px;"><?= htmlspecialchars($prod['cor']) ?></td>
                        <td style="padding:10px; color:#2ecc71;">R$ <?= number_format($prod['preco_venda'], 2, ',', '.') ?></td>
                        <td style="padding:10px;">
                            <a href="index.php?rota=excluir_produto&id=<?= (int)$prod['id'] ?>" style="color:#e74c3c; text-decoration:none;" onclick="return confirm('Desativar este produto?')">🗑️</a>
                        </td>
                    </tr>
                    <?php $numeroLinha++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="padding:20px; text-align:center; color:#888;">Nenhum produto encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top:12px; color:#aaa; font-size:13px; text-align:right;">
            Total de itens: <strong style="color:#fff;"><?= (int)($totalItens ?? 0) ?></strong>
        </div>

        <?php if (($totalPaginas ?? 1) > 1): ?>
            <div style="display:flex; justify-content:center; gap:8px; margin-top:16px; flex-wrap:wrap;">
                <?php $filtroEncoded = urlencode($filtro ?? ''); ?>
                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                    <a href="index.php?rota=produtos&pagina=<?= $p ?><?= !empty($filtro) ? '&filtro=' . $filtroEncoded : '' ?>"
                       style="padding:8px 12px; border-radius:4px; text-decoration:none; border:1px solid #555; <?= ($p === (int)$paginaAtual) ? 'background:#e6b800; color:#111; font-weight:bold;' : 'background:#222; color:#ddd;' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>