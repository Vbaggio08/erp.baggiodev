

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">👕 Cadastro de Produtos</h1>
        <a href="index.php?rota=novo_produto" class="btn-green" style="text-decoration:none;">+ Novo Produto</a>
    </div>

    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444;">
        <table style="width:100%; border-collapse: collapse; color:#ddd;">
            <thead>
                <tr style="background:#222; text-align:left;">
                    <th style="padding:10px;">SKU</th>
                    <th style="padding:10px;">Nome</th>
                    <th style="padding:10px;">Tam</th>
                    <th style="padding:10px;">Cor</th>
                    <th style="padding:10px;">Preço Venda</th>
                    <th style="padding:10px;">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($produtos as $prod): ?>
                <tr style="border-bottom:1px solid #333;">
                    <td style="padding:10px; color:#aaa; font-size:12px;"><?= $prod['sku'] ?></td>
                    <td style="padding:10px; font-weight:bold;"><?= $prod['nome'] ?></td>
                    <td style="padding:10px;"><?= $prod['tamanho'] ?></td>
                    <td style="padding:10px;"><?= $prod['cor'] ?></td>
                    <td style="padding:10px; color:#2ecc71;">R$ <?= number_format($prod['preco_venda'], 2, ',', '.') ?></td>
                    <td style="padding:10px;">
                        <a href="index.php?rota=excluir_produto&id=<?= $prod['id'] ?>" style="color:#e74c3c; text-decoration:none;" onclick="return confirm('Desativar este produto?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>