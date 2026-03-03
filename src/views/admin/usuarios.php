<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="box-relatorio">
    <h1 class="login-title">👥 Gerenciar Usuários</h1>
    
    <div style="background:var(--surface-color); padding:20px; border-radius:8px; border:1px solid #333; margin-bottom:30px;">
        <h3 style="color:#e6b800; margin-top:0;">
            <?= isset($usuarioEdit) ? '✏️ Editar Usuário' : '+ Novo Usuário' ?>
        </h3>
        
        <form action="index.php?rota=salvar_usuario" method="POST" style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap:15px; align-items:end;">
            
            <input type="hidden" name="id" value="<?= $usuarioEdit['id'] ?? '' ?>">

            <div>
                <label style="color:#aaa; font-size:12px;">Nome</label>
                <input type="text" name="nome" value="<?= $usuarioEdit['nome'] ?? '' ?>" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
            </div>

            <div>
                <label style="color:#aaa; font-size:12px;">E-mail</label>
                <input type="email" name="email" value="<?= $usuarioEdit['email'] ?? '' ?>" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
            </div>

            <div>
                <label style="color:#aaa; font-size:12px;">Senha <?= isset($usuarioEdit) ? '(Deixe em branco p/ manter)' : '' ?></label>
                <input type="password" name="senha" <?= isset($usuarioEdit) ? '' : 'required' ?> style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
            </div>

            <div>
                <label style="color:#aaa; font-size:12px;">Nível</label>
                <select name="nivel" style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                    <option value="funcionario" <?= (isset($usuarioEdit) && $usuarioEdit['nivel'] == 'funcionario') ? 'selected' : '' ?>>Funcionário</option>
                    <option value="admin" <?= (isset($usuarioEdit) && $usuarioEdit['nivel'] == 'admin') ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn-green" style="height:35px;">Salvar</button>
                <?php if(isset($usuarioEdit)): ?>
                    <a href="index.php?rota=gerenciar_usuarios" class="btn-red" style="text-decoration:none; height:35px; line-height:35px; padding:0 10px; display:inline-block;">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <table style="width:100%; border-collapse: collapse; color:#ddd;">
        <thead>
            <tr style="background:#222; text-align:left;">
                <th style="padding:10px;">Nome</th>
                <th style="padding:10px;">E-mail</th>
                <th style="padding:10px;">Nível</th>
                <th style="padding:10px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr style="border-bottom:1px solid #333;">
                    <td style="padding:10px; font-weight:bold;"><?= htmlspecialchars($u['nome']) ?></td>
                    <td style="padding:10px;"><?= htmlspecialchars($u['email']) ?></td>
                    <td style="padding:10px;"><?= strtoupper($u['nivel']) ?></td>
                    <td style="padding:10px;">
                        <a href="index.php?rota=editar_usuario&id=<?= $u['id'] ?>" style="color:#e6b800; text-decoration:none; margin-right:10px;">✏️ Editar</a>
                        
                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                            <a href="index.php?rota=excluir_usuario&id=<?= $u['id'] ?>" style="color:#e74c3c; text-decoration:none;" onclick="return confirm('Excluir?')">🗑️ Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>