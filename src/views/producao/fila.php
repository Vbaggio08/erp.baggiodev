<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <h1 class="login-title" style="margin:0;">Fila de Produção</h1>
        
        <div style="display:flex; gap: 10px;">
            <?php if (!empty($fila)): ?>
            <form action="index.php?rota=concluir_tudo" method="POST" onsubmit="return confirm('Tem certeza? Vai mover TUDO para o estoque?');">
                <button type="submit" class="login-button btn-gold" style="padding: 10px 20px;">⚡ Concluir Tudo</button>
            </form>
            <?php endif; ?>

            <a href="index.php?rota=importar_csv_producao" class="login-button" style="background:#555; color:white; text-decoration:none; padding:10px 20px;">
                📂 Importar CSV
            </a>

            <a href="index.php?rota=form_venda" class="login-button btn-green" style="text-decoration:none; padding:10px 20px;">
                + Nova Ordem
            </a>
        </div>
    </div>

    <div class="production-grid">
        <?php if (empty($fila)): ?>
            <p style="color:var(--text-medium); padding:20px; grid-column: 1 / -1; text-align:center;">
                Fila vazia. Importe um CSV ou adicione manualmente.
            </p>
        <?php else: ?>
            <?php foreach ($fila as $item): 
                $dataEntrada = strtotime($item['data_entrada']);
                $diasPassados = (time() - $dataEntrada) / (60 * 60 * 24);
                $isAtrasado = $diasPassados > 2;
                $foto = !empty($item['foto']) ? $item['foto'] : 'sem_foto.jpg';
                if(strpos($foto, 'uploads/') === false && $foto !== 'sem_foto.jpg') $foto = 'uploads/' . $foto;
            ?>
            <div class="production-card <?= $isAtrasado ? 'atrasado' : '' ?>">
                <img src="<?= htmlspecialchars($foto) ?>" alt="Produto" class="prod-img">
                <div class="prod-info">
                    <span style="font-size:10px; color:#e6b800; border:1px solid #e6b800; padding:2px 5px; border-radius:3px; display:inline-block; margin-bottom:5px; align-self: flex-start;">
                        <?= htmlspecialchars($item['empresa'] ?? 'Ripfire') ?>
                    </span>
                    <span class="prod-channel"><?= htmlspecialchars($item['canal']) ?></span>
                    <h3 class="prod-title"><?= htmlspecialchars($item['tipo']) ?></h3>
                    <div class="prod-meta">
                        <span class="meta-tag"><?= htmlspecialchars($item['tamanho']) ?></span>
                        <span class="meta-tag color-tag"><?= htmlspecialchars($item['cor']) ?></span>
                    </div>
                    <form action="index.php?rota=concluir_item" method="POST" style="margin-top:auto;">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn-concluir">✅ Concluir</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>