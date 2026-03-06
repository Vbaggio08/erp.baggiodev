<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">🏢 Fornecedores</h1>
        <a href="index.php?rota=novo_fornecedor" class="btn-green" style="text-decoration:none;">+ Novo Fornecedor</a>
    </div>

    <div style="background: var(--bg-surface-2); padding: 20px; border-radius: 8px; border: 1px solid #444;">
        <table style="width:100%; border-collapse: collapse; color:#ddd; font-size:14px;">
            <thead>
                <tr style="background:#222; text-align:left; border-bottom:2px solid #555;">
                    <th style="padding:12px;">Nome</th>
                    <th style="padding:12px;">Categoria</th>
                    <th style="padding:12px;">Contato</th>
                    <th style="padding:12px;">Email</th>
                    <th style="padding:12px; text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($fornecedores)): ?>
                    <?php foreach($fornecedores as $forn): ?>
                        <tr style="border-bottom:1px solid #333;">
                            <td style="padding:12px; font-weight:bold;"><?= htmlspecialchars($forn['nome']) ?></td>
                            <td style="padding:12px; color:#aaa;"><?= htmlspecialchars($forn['categoria'] ?? '-') ?></td>
                            <td style="padding:12px;"><?= htmlspecialchars($forn['contato'] ?? '-') ?></td>
                            <td style="padding:12px;"><?= htmlspecialchars($forn['email'] ?? '-') ?></td>
                            
                            <td style="padding:12px; text-align:center; display:flex; gap:10px; justify-content:center;">
                                <a href="index.php?rota=editar_fornecedor&id=<?= $forn['id'] ?>" class="btn-blue" style="text-decoration:none; padding:5px 10px; font-size:12px;">Editar</a>
                                <?php if(($_SESSION['user_nivel'] ?? '') === 'admin'): ?>
                                    <a href="index.php?rota=excluir_fornecedor&id=<?= $forn['id'] ?>" class="btn-red" style="text-decoration:none; padding:5px 10px; font-size:12px;" onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding:30px; text-align:center;">Nenhum fornecedor cadastrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>