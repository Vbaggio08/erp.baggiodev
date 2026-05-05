<?php
    $totalGeral = 0;
    if (!empty($_SESSION['lista_compra'])) {
        foreach ($_SESSION['lista_compra'] as $it) {
            $totalGeral += $it['total'] ?? 0;
        }
    }
?>

<div class="box-relatorio">
    <div class="page-header">
        <h1 class="login-title"><i class="bi bi-cart3 action-icon"></i>Nova Compra / Pedido</h1>
        <a href="index.php?rota=compras" class="btn-red text-decoration-none">
            <i class="bi bi-x-circle action-icon"></i>Cancelar
        </a>
    </div>

    <div class="purchase-grid">
        
        <div>
            <div class="panel mb-3">
                <h3 class="purchase-step-title">1. Dados da Compra</h3>
                
                <form action="index.php?rota=salvar_compra" method="POST" id="formSalvar">
                    <div class="purchase-form-grid mb-3">
                        <div>
                            <label class="form-label">Fornecedor</label>
                            <select name="fornecedor" required class="form-select">
                                <option value="">-- Selecione --</option>
                                <?php foreach($fornecedores as $f): ?>
                                    <option value="<?= $f['nome'] ?>"><?= $f['nome'] ?></option>
                                <?php endforeach; ?>
                                <option value="Avulso">Fornecedor Avulso</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Data Compra</label>
                            <input type="date" name="data_compra" value="<?= date('Y-m-d') ?>" class="form-control">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Observações</label>
                        <input type="text" name="obs" class="form-control" placeholder="Ex: Reposição de estoque camiseta preta">
                    </div>
                </form>
            </div>

            <div class="panel">
                <h3 class="purchase-step-title">2. Adicionar Produtos</h3>
                
                <form action="index.php?rota=compra_adicionar" method="POST">
                    <div class="purchase-item-row">
                        <div class="product-col">
                            <label class="form-label">Nome do Produto / Material</label>
                            <input type="text" name="produto" placeholder="Ex: Tinta DTF Branca 1L" required 
                                   class="form-control">
                        </div>
                        <div class="qty-col">
                            <label class="form-label">Qtd</label>
                            <input type="number" name="qtd" value="1" step="0.01" required 
                                   class="form-control text-center">
                        </div>
                        <div class="value-col">
                            <label class="form-label">Custo (R$)</label>
                            <input type="text" name="valor" placeholder="0,00" required 
                                   class="form-control">
                        </div>
                        <button type="submit" class="btn-green add-item-btn"><i class="bi bi-plus-circle action-icon"></i>Add</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="panel purchase-list-panel">
            <h3 class="purchase-step-title">3. Lista de Compra</h3>

            <div class="purchase-list-scroll">
                <?php if (empty($_SESSION['lista_compra'])): ?>
                    <p class="text-muted text-center py-4">Carrinho vazio.</p>
                <?php else: ?>
                    <table class="purchase-mini-table">
                        <?php foreach ($_SESSION['lista_compra'] as $idx => $item): ?>
                        <tr>
                            <td>
                                <strong class="d-block"><?= htmlspecialchars($item['produto']) ?></strong>
                                <span class="text-muted small">
                                    <?= $item['qtd'] ?> x R$ <?= number_format($item['valor'] ?? 0, 2, ',', '.') ?>
                                </span>
                            </td>
                            <td class="text-end text-success-strong">
                                R$ <?= number_format($item['total'] ?? 0, 2, ',', '.') ?>
                            </td>
                            <td class="text-end remove-col">
                                <form action="index.php?rota=compra_remover" method="POST">
                                    <input type="hidden" name="index" value="<?= $idx ?>">
                                    <button class="remove-item-btn" aria-label="Remover item"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>

            <div class="purchase-total-box">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">TOTAL:</span>
                    <span class="purchase-total-value">R$ <?= number_format($totalGeral, 2, ',', '.') ?></span>
                </div>
            </div>

            <button type="button" onclick="document.getElementById('formSalvar').submit()" 
                    class="btn-green w-100 py-3 fs-6">
                <i class="bi bi-check2-circle action-icon"></i>Finalizar Pedido
            </button>
            
            <a href="index.php?rota=compra_limpar" class="d-block text-center text-danger text-decoration-none mt-3 small">Limpar Carrinho</a>
        </div>
    </div>
</div>

<style>
    .purchase-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
    }

    .purchase-step-title {
        margin: 0 0 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
        color: var(--primary-color);
        font-size: 1rem;
    }

    .purchase-form-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 15px;
    }

    .purchase-item-row {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }

    .product-col {
        flex: 3;
    }

    .qty-col,
    .value-col {
        flex: 1;
    }

    .add-item-btn {
        height: 42px;
    }

    .purchase-list-panel {
        height: fit-content;
    }

    .purchase-list-scroll {
        max-height: 300px;
        overflow-y: auto;
        margin-bottom: 20px;
    }

    .purchase-mini-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .purchase-mini-table tr {
        border-bottom: 1px solid var(--border-color);
    }

    .purchase-mini-table td {
        padding: 8px 0;
    }

    .remove-col {
        width: 24px;
    }

    .remove-item-btn {
        background: none;
        border: none;
        color: var(--brand-red);
        cursor: pointer;
        padding: 2px;
    }

    .purchase-total-box {
        background: var(--surface-elevated);
        padding: 15px;
        border-radius: var(--radius-sm);
        margin-bottom: 15px;
    }

    .purchase-total-value {
        color: var(--primary-color);
        font-size: 20px;
        font-weight: 700;
    }

    .text-success-strong {
        color: var(--brand-green);
    }

    @media (max-width: 992px) {
        .purchase-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .purchase-form-grid,
        .purchase-item-row {
            grid-template-columns: 1fr;
            display: grid;
        }

        .add-item-btn {
            width: 100%;
        }
    }
</style>