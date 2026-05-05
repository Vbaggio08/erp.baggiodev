
<div class="box-relatorio">
    <h1 class="login-title"><i class="bi bi-tag action-icon"></i>Cadastro de Produtos</h1>

    <div class="panel" style="margin-bottom: 30px;">
        <form action="index.php?rota=salvar_produto" method="POST" class="product-form-grid">
            
            <div>
                <label class="form-label text-warning fw-bold">SKU (Código)</label>
                <input type="text" name="sku" class="form-control" placeholder="CAM-001" required>
            </div>

            <div>
                <label class="form-label">Nome da Peça</label>
                <input type="text" name="nome" class="form-control" placeholder="Ex: Camiseta Oversized" required>
            </div>
            
            <div>
                <label class="form-label">Tamanho</label>
                <input type="text" name="tamanho" class="form-control" placeholder="M" required>
            </div>
            
            <div>
                <label class="form-label">Cor</label>
                <input type="text" name="cor" class="form-control" placeholder="Preta">
            </div>
            
            <div>
                <label class="form-label">Custo (R$)</label>
                <input type="number" step="0.01" name="preco_custo" class="form-control">
            </div>
            
            <div>
                <label class="form-label">Venda (R$)</label>
                <input type="number" step="0.01" name="preco_venda" class="form-control">
            </div>

            <button type="submit" class="btn-green product-submit-btn">
                <i class="bi bi-plus-circle action-icon"></i>Cadastrar
            </button>
        </form>
    </div>

    <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Produto</th>
                    <th>Tam</th>
                    <th>Cor</th>
                    <th>Venda</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($produtos)): ?>
                    <?php foreach($produtos as $p): ?>
                    <tr>
                        <td class="text-muted small"><?= htmlspecialchars($p['sku']) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($p['nome']) ?></td>
                        <td><?= htmlspecialchars($p['tamanho']) ?></td>
                        <td><?= htmlspecialchars($p['cor']) ?></td>
                        <td>R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></td>
                        <td>
                            <a href="index.php?rota=excluir_produto&id=<?= $p['id'] ?>" class="text-danger text-decoration-none" onclick="return confirm('Excluir este produto?')">
                                <i class="bi bi-trash3 action-icon"></i>Excluir
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center p-4">Nenhum produto cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<style>
    .product-form-grid {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr 1fr 1fr 1fr auto;
        gap: 15px;
        align-items: end;
    }

    .product-submit-btn {
        height: 42px;
    }

    @media (max-width: 1199px) {
        .product-form-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .product-form-grid {
            grid-template-columns: 1fr;
        }

        .product-submit-btn {
            width: 100%;
        }
    }
</style>
</body>
</html>