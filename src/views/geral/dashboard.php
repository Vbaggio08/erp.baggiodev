<?php include __DIR__ . '/header.php'; ?>

<div class="main-content">
    <h1 class="login-title">📊 Visão Geral</h1>
    <p class="login-subtitle">Bem-vindo, <strong><?= $_SESSION['user_name'] ?></strong>!</p>

    <div class="kpi-grid">
        
        <div class="kpi-card-inline" style="border-left-color: #f39c12;">
            <div class="kpi-header">
                <h3>Pedidos Pendentes</h3>
                <span class="material-icons" style="color:#f39c12;">pending_actions</span>
            </div>
            <p class="kpi-value" style="color:#f39c12;"><?= $totalPendentes ?? 0 ?></p>
            <small class="kpi-subtext">Aguardando produção</small>
        </div>

        <div class="kpi-card-inline" style="border-left-color: #2ecc71;">
             <div class="kpi-header">
                <h3>Peças em Estoque</h3>
                <span class="material-icons" style="color:#2ecc71;">inventory_2</span>
            </div>
            <p class="kpi-value" style="color:#2ecc71;"><?= $totalPecas ?? 0 ?></p>
            <small class="kpi-subtext">Saldo geral físico</small>
        </div>

        <div class="kpi-card-inline" style="border-left-color: #3498db;">
             <div class="kpi-header">
                <h3>Produzidos Hoje</h3>
                <span class="material-icons" style="color:#3498db;">check_circle</span>
            </div>
            <p class="kpi-value" style="color:#3498db;"><?= $produzidosHoje ?? 0 ?></p>
            <small class="kpi-subtext">Baseado nas Fichas</small>
        </div>

        <div class="kpi-card-inline" style="border-left-color: #e74c3c;">
             <div class="kpi-header">
                <h3>Perdas Registradas</h3>
                <span class="material-icons" style="color:#e74c3c;">warning</span>
            </div>
            <p class="kpi-value" style="color:#e74c3c;"><?= $totalPerdas ?? 0 ?></p>
            <small class="kpi-subtext">Itens descartados</small>
        </div>
    </div>

    <div class="dashboard-grid">
        
        <div class="widget-card">
            <div class="widget-header">
                <h3 class="widget-title" style="color:#e74c3c;">📉 Estoque Baixo (&lt; 5)</h3>
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
            <h3 class="widget-title">⚡ Acesso Rápido</h3>
            <div class="quick-access-grid">
                <a href="index.php?rota=novo_gabarito" class="quick-access-link">
                    <span class="material-icons icon-gold">description</span> 
                    <span>Criar Nova Ficha Técnica</span>
                </a>
                <a href="index.php?rota=entrada" class="quick-access-link">
                    <span class="material-icons icon-green">add_circle</span> 
                    <span>Dar Entrada no Estoque</span>
                </a>
                <a href="index.php?rota=relatorio_perdas" class="quick-access-link">
                    <span class="material-icons icon-red">warning</span> 
                    <span>Registrar Perda/Quebra</span>
                </a>
            </div>
        </div>

    </div>
</div>

</body>
</html>