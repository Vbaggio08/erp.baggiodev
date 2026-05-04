

<style>
    /* Estilos dos Botões de Status */
    .status-select {
        padding: 6px 10px;
        border-radius: 15px;
        border: none;
        font-weight: bold;
        cursor: pointer;
        color: #fff;
        text-align: center;
        width: 100%;
        max-width: 130px;
        font-size: 12px;
        appearance: none;
        -webkit-appearance: none;
        text-align-last: center;
        transition: 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    /* Cores por Status */
    .status-Pendente { background-color: #f39c12; }       /* Laranja */
    .status-EmAndamento { background-color: #3498db; }    /* Azul */
    .status-Aguardando { background-color: #9b59b6; }     /* Roxo */
    .status-Concluido { background-color: #2ecc71; }      /* Verde */
    .status-Cancelado { background-color: #e74c3c; }      /* Vermelho */

    .status-select:hover { opacity: 0.9; transform: scale(1.02); }
</style>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">🛠️ Ordens de Serviço</h1>
        <a href="index.php?rota=nova_os" class="btn-green" style="text-decoration:none;">+ Nova O.S.</a>
    </div>

    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444;">
        <table style="width:100%; border-collapse: collapse; color:#ddd;">
            <thead>
                <tr style="background:#222; text-align:left;">
                    <th style="padding:12px;">ID</th>
                    <th style="padding:12px;">Data</th>
                    <th style="padding:12px;">Empresa / Prestador</th>
                    <th style="padding:12px;">Resumo</th>
                    <th style="padding:12px;">Total</th>
                    <th style="padding:12px; text-align:center;">Status</th>
                    <th style="padding:12px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($servicos)): ?>
                    <?php foreach($servicos as $os): ?>
                    
                    <?php $classeStatus = str_replace(' ', '', $os['status'] ?? 'Pendente'); ?>

                    <tr style="border-bottom:1px solid #333;">
                        <td style="padding:12px; color:#aaa;">#<?= $os['id'] ?></td>
                        <td style="padding:12px;">
                            <?= !empty($os['data_abertura']) ? date('d/m/Y', strtotime($os['data_abertura'])) : '-' ?>
                        </td>
                        <td style="padding:12px;">
                            <div style="font-size:12px; color:#aaa;">De: <?= htmlspecialchars($os['empresa'] ?? '') ?></div>
                            <div style="font-weight:bold; color:#e6b800;">Para: <?= htmlspecialchars($os['prestador'] ?? '') ?></div>
                        </td>
                        <td style="padding:12px; color:#ccc;">
                            <?= htmlspecialchars(substr($os['descricao'], 0, 30)) ?>...
                        </td>
                        <td style="padding:12px; color:#2ecc71; font-weight:bold;">
                            R$ <?= number_format($os['valor_total'] ?? 0, 2, ',', '.') ?>
                        </td>
                        
                        <td style="padding:12px; text-align:center;">
                            <select class="status-select status-<?= $classeStatus ?>" 
                                    onchange="mudarStatusOS(this, <?= $os['id'] ?>)">
                                
                                <option value="Pendente" <?= $os['status'] == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="Em Andamento" <?= $os['status'] == 'Em Andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                <option value="Aguardando" <?= $os['status'] == 'Aguardando' ? 'selected' : '' ?>>Aguardando Peça</option>
                                <option value="Concluido" <?= $os['status'] == 'Concluido' ? 'selected' : '' ?>>✅ Concluído</option>
                                <option value="Cancelado" <?= $os['status'] == 'Cancelado' ? 'selected' : '' ?>>❌ Cancelado</option>
                            </select>
                        </td>

                        <td style="padding:12px;">
                             <?php if(isset($_SESSION['user_nivel']) && $_SESSION['user_nivel'] === 'admin'): ?>
                                <a href="index.php?rota=os_excluir&id=<?= $os['id'] ?>" 
                                   onclick="return confirm('Tem certeza?')"
                                   style="color:#e74c3c; text-decoration:none;">
                                    <span class="material-icons">delete</span>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:30px;">Nenhuma O.S. encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function mudarStatusOS(select, id) {
    var novoStatus = select.value;
    
    // Atualiza a cor visualmente na hora (Remove espaços para achar a classe CSS)
    var classeCor = novoStatus.replace(/\s+/g, '');
    select.className = 'status-select status-' + classeCor;
    
    // Envia para o servidor salvar
    window.location.href = 'index.php?rota=os_mudar_status&id=' + id + '&status=' + novoStatus;
}
</script>

</body>
</html>