<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">🖨️ Ver Produção DTF</h1>
        <a href="index.php?rota=novo_dtf" class="btn-green" style="text-decoration:none;">+ Nova Produção DTF</a>
    </div>

    <div style="margin-bottom:15px;">
        <input type="text" id="filtroDtf" placeholder="🔍 Pesquisar por cliente, nº pedido, plataforma..."
               oninput="filtrarTabelaDtf()"
               style="width:100%; max-width:450px; padding:10px 14px; background:#222; border:1px solid #555; color:#fff; border-radius:6px; font-size:14px;">
    </div>

    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444; overflow-x:auto;">
        <table id="tabelaDtf" style="width:100%; border-collapse: collapse; color:#ddd; font-size:14px;">
            <thead>
                <tr style="background:#222; text-align:left; border-bottom:2px solid #444;">
                    <th style="padding:12px;">Pedido</th>
                    <th style="padding:12px;">Cliente</th>
                    <th style="padding:12px;">Vendedor</th>
                    <th style="padding:12px;">Plataforma</th>
                    <th style="padding:12px; text-align:center;">Metros</th>
                    <th style="padding:12px; text-align:right;">Valor Final</th>
                    <th style="padding:12px;">Data Pedido</th>
                    <th style="padding:12px;">Previsão Entrega</th>
                    <th style="padding:12px;">Pagamento</th>
                    <th style="padding:12px; text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pedidosDtf)): ?>
                    <?php foreach($pedidosDtf as $d): ?>
                    <tr style="border-bottom:1px solid #333; transition:0.2s;"
                        onmouseover="this.style.background='#2a2a2a'"
                        onmouseout="this.style.background='transparent'">

                        <td style="padding:12px;">
                            <strong style="color:#e6b800;">#<?= htmlspecialchars($d['numero_pedido'] ?? $d['id']) ?></strong>
                        </td>

                        <td style="padding:12px;">
                            <?= htmlspecialchars($d['cliente'] ?? '') ?><br>
                            <span style="font-size:11px; color:#777;"><?= htmlspecialchars($d['contato'] ?? '') ?></span>
                        </td>

                        <td style="padding:12px; color:#aaa;">
                            <?= htmlspecialchars($d['vendedor_nome'] ?? 'N/A') ?>
                        </td>

                        <td style="padding:12px; color:#aaa;">
                            <?= htmlspecialchars($d['plataforma'] ?? '--') ?>
                        </td>

                        <td style="padding:12px; text-align:center;">
                            <div style="background:#333; display:inline-block; padding:4px 10px; border-radius:4px; font-weight:bold; color:#3498db;">
                                <?= number_format((float)($d['metros'] ?? 0), 2, ',', '.') ?>m
                            </div>
                        </td>

                        <td style="padding:12px; text-align:right; color:#2ecc71; font-weight:bold;">
                            R$ <?= number_format((float)($d['valor_final'] ?? 0), 2, ',', '.') ?>
                        </td>

                        <td style="padding:12px;">
                            <?= !empty($d['data_pedido']) ? date('d/m/Y', strtotime($d['data_pedido'])) : '--' ?>
                        </td>

                        <td style="padding:12px;">
                            <?php if(!empty($d['data_entrega']) && $d['data_entrega'] != '0000-00-00'): ?>
                                <?= date('d/m/Y', strtotime($d['data_entrega'])) ?>
                            <?php else: ?>
                                <span style="color:#666;">--/--/--</span>
                            <?php endif; ?>
                        </td>

                        <td style="padding:12px; color:#aaa;">
                            <?= htmlspecialchars($d['meio_pagamento'] ?? '') ?>
                        </td>

                        <td style="padding:12px; text-align:center;">
                            <div style="display:flex; gap:12px; justify-content:center; align-items:center;">

                                <?php if(!empty($d['arquivo_impressao'])): ?>
                                    <a href="assets/uploads/<?= htmlspecialchars($d['arquivo_impressao']) ?>" target="_blank"
                                       title="Ver Arquivo Impressão" style="color:#3498db;">
                                        <span class="material-icons" style="font-size:20px;">image</span>
                                    </a>
                                <?php endif; ?>

                                <?php if(!empty($d['caminho_comprovante'])): ?>
                                    <a href="assets/uploads/comprovantes/<?= htmlspecialchars($d['caminho_comprovante']) ?>" target="_blank"
                                       title="Ver Comprovante" style="color:#2ecc71;">
                                        <span class="material-icons" style="font-size:20px;">receipt_long</span>
                                    </a>
                                <?php endif; ?>

                                <a href="index.php?rota=novo_dtf&id=<?= $d['id'] ?>" title="Editar" style="color:#aaa;">
                                    <span class="material-icons" style="font-size:20px;">edit</span>
                                </a>

                                <?php if(isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] === 'admin'): ?>
                                    <a href="index.php?rota=excluir_dtf&id=<?= $d['id'] ?>"
                                       onclick="return confirm('Excluir este pedido DTF?')"
                                       title="Excluir"
                                       style="color:#e74c3c;">
                                        <span class="material-icons" style="font-size:20px;">delete</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align:center; padding:40px; color:#666;">
                            Nenhum pedido DTF encontrado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filtrarTabelaDtf() {
    var termo = document.getElementById('filtroDtf').value.toLowerCase();
    var linhas = document.querySelectorAll('#tabelaDtf tbody tr');
    linhas.forEach(function(tr) {
        var texto = tr.textContent.toLowerCase();
        tr.style.display = texto.includes(termo) ? '' : 'none';
    });
}
</script>
