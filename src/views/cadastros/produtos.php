<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="box-relatorio">
    <h1 class="login-title">👕 Cadastro de Produtos</h1>

    <div style="background:var(--surface-color); padding:20px; border-radius:8px; border:1px solid #333; margin-bottom:30px;">
        <form action="index.php?rota=salvar_produto" method="POST" style="display:grid; grid-template-columns: 1fr 2fr 1fr 1fr 1fr 1fr auto; gap:15px; align-items:end;">
            
            <div>
                <label style="color:#e6b800; font-size:12px; font-weight:bold;">SKU (Código)</label>
                <input type="text" name="sku" placeholder="CAM-001" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
            </div>

            <div>
                <label style="color:#aaa; font-size:12px;">Nome da Peça</label>
                <input type="text" name="nome" placeholder="Ex: Camiseta Oversized" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
            </div>
            
            <div>
                <label style="color:#aaa; font-size:12px;">Tamanho</label>
                <input type="text" name="tamanho" placeholder="M" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
            </div>
            
            <div>
                <label style="color:#aaa; font-size:12px;">Cor</label>
                <input type="text" name="cor" placeholder="Preta" style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
            </div>
            
            <div>
                <label style="color:#aaa; font-size:12px;">Custo (R$)</label>
                <input type="number" step="0.01" name="preco_custo" style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
            </div>
            
            <div>
                <label style="color:#aaa; font-size:12px;">Venda (R$)</label>
                <input type="number" step="0.01" name="preco_venda" style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
            </div>

            <button type="submit" class="btn-green" style="height:35px;">Cadastrar</button>
        </form>
    </div>

    <table style="width:100%; border-collapse: collapse; color:#ddd;">
        <thead>
            <tr style="background:#222; text-align:left;">
                <th style="padding:10px;">SKU</th>
                <th style="padding:10px;">Produto</th>
                <th style="padding:10px;">Tam</th>
                <th style="padding:10px;">Cor</th>
                <th style="padding:10px;">Venda</th>
                <th style="padding:10px;">Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($produtos)): ?>
                <?php foreach($produtos as $p): ?>
                <tr style="border-bottom:1px solid #333;">
                    <td style="padding:10px; color:#888; font-size:12px;"><?= htmlspecialchars($p['sku']) ?></td>
                    <td style="padding:10px; font-weight:bold;"><?= htmlspecialchars($p['nome']) ?></td>
                    <td style="padding:10px;"><?= htmlspecialchars($p['tamanho']) ?></td>
                    <td style="padding:10px;"><?= htmlspecialchars($p['cor']) ?></td>
                    <td style="padding:10px;">R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></td>
                    <td style="padding:10px;">
                        <a href="index.php?rota=excluir_produto&id=<?= $p['id'] ?>" style="color:#e74c3c; text-decoration:none;" onclick="return confirm('Excluir este produto?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center; padding:20px;">Nenhum produto cadastrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>