<style>
    .status-select {
        padding: 6px 10px; border-radius: 15px; border: none; font-weight: bold; cursor: pointer; color: #fff; text-align: center; width: 100%; max-width: 140px; font-size: 11px; appearance: none; text-align-last: center; transition: 0.2s;
    }
    .status-Mockup { background-color: #7f8c8d; }
    .status-Impresso { background-color: #3498db; }
    .status-Estampado { background-color: #9b59b6; }
    .status-Enviado { background-color: #2ecc71; }
    .status-select:hover { opacity: 0.9; transform: scale(1.02); }
</style>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">📋 Linha de Produção (Fichas)</h1>
        <a href="index.php?rota=novo_gabarito" class="btn-green" style="text-decoration:none;">+ Novo Pedido</a>
    </div>

    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444;">
        <table style="width:100%; border-collapse: collapse; color:#ddd; font-size:14px;">
            <thead>
                <tr style="background:#222; text-align:left; border-bottom:2px solid #444;">
                    <th style="padding:12px;">Pedido</th>
                    <th style="padding:12px;">Cliente</th>
                    <th style="padding:12px;">Vendedor</th>
                    <th style="padding:12px;">Modelos Agrupados</th>
                    <th style="padding:12px;">Cores</th>
                    <th style="padding:12px; text-align:center;">Total Peças</th>
                    <th style="padding:12px;">Entrega</th>
                    <th style="padding:12px;">Pagamento</th>
                    <th style="padding:12px; text-align:center;">Status</th>
                    <th style="padding:12px; text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($fichas)): ?>
                    <?php foreach($fichas as $f): ?>
                    
                    <?php $classeStatus = str_replace(' ', '', $f['status'] ?? 'Mockup'); ?>

                    <tr style="border-bottom:1px solid #333; transition: 0.3s;" onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background='transparent'">
                        
                        <td style="padding:12px;">
                            <strong style="color:#e6b800;">#<?= htmlspecialchars($f['numero_pedido'] ?? $f['id'] ?? '') ?></strong>
                        </td>

                        <td style="padding:12px;">
                            <?= htmlspecialchars($f['cliente'] ?? '') ?><br>
                            <span style="font-size:11px; color:#777;"><?= htmlspecialchars($f['plataforma'] ?? 'Direto') ?></span>
                        </td>

                        <td style="padding:12px;">
                            <?= htmlspecialchars($f['vendedor_nome'] ?? 'N/A') ?>
                        </td>
                        
                        <td style="padding:12px; color:#fff;">
                            <div style="max-width:250px; line-height:1.4;">
                                <?= htmlspecialchars(($f['modelos_agrupados'] ?? $f['modelo']) ?? '') ?>
                            </div>
                        </td>
                        
                        <td style="padding:12px; color:#aaa;">
                            <?= htmlspecialchars(($f['cores_agrupadas'] ?? $f['cor']) ?? '') ?>
                        </td>

                        <td style="padding:12px; text-align:center;">
                            <div style="background:#333; display:inline-block; padding:4px 10px; border-radius:4px; font-weight:bold; color:#e6b800;">
                                <?= $f['total_pecas_pedido'] ?? $f['quantidade'] ?? 0 ?>
                            </div>
                        </td>

                        <td style="padding:12px;">
                            <?php if(!empty($f['data_entrega']) && $f['data_entrega'] != '0000-00-00'): ?>
                                <?= date('d/m/Y', strtotime($f['data_entrega'])) ?>
                            <?php else: ?>
                                <span style="color:#666;">--/--/--</span>
                            <?php endif; ?>
                        </td>

                        <td style="padding:12px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="color: #aaa;"><?= htmlspecialchars($f['meio_pagamento'] ?? '') ?></span>
                                <?php if (!empty($f['caminho_comprovante'])): ?>
                                    <a href="assets/uploads/comprovantes/<?= $f['caminho_comprovante'] ?>" target="_blank" title="Ver Comprovante" style="color:#2ecc71;">
                                        <span class="material-icons" style="font-size:18px;">receipt_long</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td style="padding:12px; text-align:center;">
                            <select class="status-select status-<?= $classeStatus ?>" 
                                    onchange="mudarStatusFicha(this, <?= $f['id'] ?>)">
                                <option value="Mockup" <?= ($f['status'] ?? '') == 'Mockup' ? 'selected' : '' ?>>🖼️ Mockup</option>
                                <option value="Impresso" <?= ($f['status'] ?? '') == 'Impresso' ? 'selected' : '' ?>>🖨️ Impresso</option>
                                <option value="Estampado" <?= ($f['status'] ?? '') == 'Estampado' ? 'selected' : '' ?>>👕 Estampado</option>
                                <option value="Enviado" <?= ($f['status'] ?? '') == 'Enviado' ? 'selected' : '' ?>>🚀 Enviado</option>
                            </select>
                        </td>

                        <td style="padding:12px; text-align:center;">
                            <div style="display:flex; gap:12px; justify-content:center; align-items:center;">
                                <a href="index.php?rota=editar_gabarito&id=<?= $f['id'] ?>" title="Editar item" style="color:#aaa;">
                                    <span class="material-icons" style="font-size:20px;">edit</span>
                                </a>

                                <a href="index.php?rota=imprimir_gabarito&id=<?= $f['id'] ?>" title="Imprimir Pedido Completo" style="color:#3498db;">
                                    <span class="material-icons" style="font-size:20px;">print</span>
                                </a>

                                <?php if(isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] === 'admin'): ?>
                                    <a href="index.php?rota=excluir_gabarito&id=<?= $f['id'] ?>" 
                                       onclick="return confirm('Excluir este pedido?')" 
                                       style="color:#e74c3c;">
                                        <span class="material-icons" style="font-size:20px;">delete</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="text-align:center; padding:40px; color:#666;">Nenhum pedido encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function mudarStatusFicha(select, id) {
    var novoStatus = select.value;
    select.className = 'status-select status-' + novoStatus.replace(/\s+/g, '');
    window.location.href = 'index.php?rota=mudar_status_gabarito&id=' + id + '&status=' + novoStatus;
}
</script>