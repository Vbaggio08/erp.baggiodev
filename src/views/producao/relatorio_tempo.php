<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <div>
            <h1 class="login-title" style="margin:0;">Produtividade Diária</h1>
            <p class="login-subtitle" style="margin:0;">Itens concluídos nos últimos 30 dias</p>
        </div>
        <a href="index.php?rota=fila_producao" class="login-button btn-gold" style="width:auto; padding: 10px 20px; text-decoration:none; color:#333;">
            🧵 Voltar para Fila
        </a>
    </div>

    <table class="stock-table" style="max-width: 600px; margin: 0 auto;">
        <thead>
            <tr>
                <th style="text-align:center;">Data</th>
                <th style="text-align:center;">Peças Produzidas</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($dados)): ?>
                <tr><td colspan="3" style="text-align:center; padding:30px;">Nenhuma produção concluída recentemente.</td></tr>
            <?php else: ?>
                <?php foreach ($dados as $d): ?>
                <tr>
                    <td style="text-align:center; font-weight:bold;">
                        <?= date('d/m/Y', strtotime($d['data'])) ?>
                    </td>
                    <td style="text-align:center; font-size:1.2rem; color:var(--brand-green); font-weight:bold;">
                        <?= $d['qtd'] ?> Peças
                    </td>
                    <td style="text-align:center;">
                        <?php if($d['qtd'] >= 10): ?>
                            <span style="color:#2ecc71;">🔥 Alta</span>
                        <?php else: ?>
                            <span style="color:#f1c40f;">⚡ Normal</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div style="text-align:center; margin-top:20px; color:#666; font-size:12px;">
        * Dados baseados na data de conclusão na Fila de Produção.
    </div>
</div>
</body>
</html>