<div class="main-content">
    <h1 class="login-title"><i class="bi bi-speedometer2 action-icon"></i>Visão Geral</h1>
    <p class="login-subtitle">Bem-vindo, <strong><?= $_SESSION['user_name'] ?></strong>!</p>

    <div class="kpi-grid">
        
        <div class="kpi-card-inline border-warning-soft">
            <div class="kpi-header">
                <h3>Pedidos Pendentes</h3>
                <i class="bi bi-hourglass-split icon-gold"></i>
            </div>
            <p class="kpi-value icon-gold"><?= $totalPendentes ?? 0 ?></p>
            <small class="kpi-subtext">Aguardando produção</small>
        </div>

        <div class="kpi-card-inline border-success-soft">
             <div class="kpi-header">
                <h3>Peças em Estoque</h3>
                <i class="bi bi-box-seam icon-green"></i>
            </div>
            <p class="kpi-value icon-green"><?= $totalPecas ?? 0 ?></p>
            <small class="kpi-subtext">Saldo geral físico</small>
        </div>

        <div class="kpi-card-inline border-info-soft">
             <div class="kpi-header">
                <h3>Produzidos Hoje</h3>
                <i class="bi bi-check2-circle icon-info"></i>
            </div>
            <p class="kpi-value icon-info"><?= $produzidosHoje ?? 0 ?></p>
            <small class="kpi-subtext">Baseado nas Fichas</small>
        </div>

        <div class="kpi-card-inline border-danger-soft">
             <div class="kpi-header">
                <h3>Perdas Registradas</h3>
                <i class="bi bi-exclamation-triangle icon-red"></i>
            </div>
            <p class="kpi-value icon-red"><?= $totalPerdas ?? 0 ?></p>
            <small class="kpi-subtext">Itens descartados</small>
        </div>
    </div>

    <div class="dashboard-grid">
        
        <div class="widget-card">
            <div class="widget-header">
                <h3 class="widget-title icon-red"><i class="bi bi-graph-down-arrow action-icon"></i>Estoque Baixo (&lt; 5)</h3>
                <a href="index.php?rota=entrada" class="widget-link">Repor Estoque</a>
            </div>
            
            <table class="dashboard-table">
                <tbody>
                    <?php if(!empty($estoqueBaixo)): ?>
                        <?php foreach($estoqueBaixo as $item): ?>
                        <tr>
                            <td><?= $item['produto'] ?></td>
                            <td><?= $item['tamanho'] ?></td>
                            <td class="text-danger">
                                Restam: <?= $item['saldo'] ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted">Nenhum item em nível crítico.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="widget-card">
            <h3 class="widget-title"><i class="bi bi-lightning-charge action-icon"></i>Acesso Rápido</h3>
            <div class="quick-access-grid">
                <a href="index.php?rota=novo_gabarito" class="quick-access-link">
                    <i class="bi bi-file-earmark-plus icon-gold"></i>
                    <span>Criar Nova Ficha Técnica</span>
                </a>
                <a href="index.php?rota=entrada" class="quick-access-link">
                    <i class="bi bi-arrow-left-right icon-green"></i>
                    <span>Dar Entrada no Estoque</span>
                </a>
                <a href="index.php?rota=relatorio_perdas" class="quick-access-link">
                    <i class="bi bi-exclamation-triangle icon-red"></i>
                    <span>Registrar Perda/Quebra</span>
                </a>
            </div>
        </div>

    </div>
</div>