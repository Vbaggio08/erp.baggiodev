
<div class="box-relatorio">
    <h1 class="login-title">👥 Gerenciar Usuários</h1>
    
    <div style="background:var(--surface-color); padding:20px; border-radius:8px; border:1px solid #333; margin-bottom:30px;">
        <h3 style="color:#e6b800; margin-top:0;">
            <?= isset($usuarioEdit) ? '✏️ Editar Usuário' : '+ Novo Usuário' ?>
        </h3>
        
        <form action="index.php?rota=salvar_usuario" method="POST">
            
            <input type="hidden" name="id" value="<?= $usuarioEdit['id'] ?? '' ?>">

            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label style="color:#aaa; font-size:12px;">Nome</label>
                    <input type="text" name="nome" value="<?= $usuarioEdit['nome'] ?? '' ?>" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                </div>

                <div>
                    <label style="color:#aaa; font-size:12px;">Usuário (login)</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($usuarioEdit['username'] ?? '') ?>" placeholder="ex: joao.silva" style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                </div>

                <div>
                    <label style="color:#aaa; font-size:12px;">E-mail</label>
                    <input type="email" name="email" value="<?= $usuarioEdit['email'] ?? '' ?>" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                </div>

                <div>
                    <label style="color:#aaa; font-size:12px;">CPF</label>
                    <input type="text" name="cpf" value="<?= htmlspecialchars($usuarioEdit['cpf'] ?? '') ?>" required maxlength="14" placeholder="000.000.000-00" style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                </div>

                <div>
                    <label style="color:#aaa; font-size:12px;">Nível</label>
                    <select name="nivel" style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                        <option value="funcionario" <?= (isset($usuarioEdit) && $usuarioEdit['nivel'] == 'funcionario') ? 'selected' : '' ?>>Funcionário</option>
                        <option value="admin" <?= (isset($usuarioEdit) && $usuarioEdit['nivel'] == 'admin') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label style="color:#aaa; font-size:12px;">Senha <?= isset($usuarioEdit) ? '(Deixe em branco p/ manter)' : '' ?></label>
                    <input type="password" name="senha" <?= isset($usuarioEdit) ? '' : 'required' ?> style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                </div>

                <div>
                    <label style="color:#aaa; font-size:12px;">Digite novamente a senha <?= isset($usuarioEdit) ? '(Obrigatório se alterar senha)' : '' ?></label>
                    <input type="password" name="confirmar_senha" <?= isset($usuarioEdit) ? '' : 'required' ?> style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                </div>
            </div>

            <!-- Horário de Trabalho -->
            <div style="background:#1a1a2e; padding:15px; border-radius:6px; border:1px solid #444; margin-bottom:15px;">
                <h4 style="color:#e6b800; margin:0 0 10px 0; font-size:14px;">⏰ Horário de Trabalho</h4>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:15px;">
                    <div>
                        <label style="color:#aaa; font-size:12px;">Entrada 1 (Manhã)</label>
                        <input type="time" name="horario_entrada_1" value="<?= $usuarioEdit['horario_entrada_1'] ?? '08:00' ?>" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                    <div>
                        <label style="color:#aaa; font-size:12px;">Saída 1 (Almoço)</label>
                        <input type="time" name="horario_saida_1" value="<?= $usuarioEdit['horario_saida_1'] ?? '12:00' ?>" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                    <div>
                        <label style="color:#aaa; font-size:12px;">Entrada 2 (Tarde)</label>
                        <input type="time" name="horario_entrada_2" value="<?= $usuarioEdit['horario_entrada_2'] ?? '13:00' ?>" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                    <div>
                        <label style="color:#aaa; font-size:12px;">Saída 2 (Fim)</label>
                        <input type="time" name="horario_saida_2" value="<?= $usuarioEdit['horario_saida_2'] ?? '18:00' ?>" required style="width:100%; padding:8px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:10px;">
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
                <th style="padding:10px;">Usuário</th>
                <th style="padding:10px;">E-mail</th>
                <th style="padding:10px;">CPF</th>
                <th style="padding:10px;">Nível</th>
                <th style="padding:10px;">Horário</th>
                <th style="padding:10px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr style="border-bottom:1px solid #333;">
                    <td style="padding:10px; font-weight:bold;"><?= htmlspecialchars($u['nome']) ?></td>
                    <td style="padding:10px;">
                        <?php if (!empty($u['username'])): ?>
                            <?= htmlspecialchars($u['username']) ?>
                        <?php else: ?>
                            <span style="color:#f39c12; font-weight:bold;">PENDENTE</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px;"><?= htmlspecialchars($u['email']) ?></td>
                    <td style="padding:10px;">
                        <?php if (!empty($u['cpf'])): ?>
                            <?= htmlspecialchars($u['cpf']) ?>
                        <?php else: ?>
                            <span style="color:#f39c12; font-weight:bold;">PENDENTE</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px;"><?= strtoupper($u['nivel']) ?></td>
                    <td style="padding:10px; font-size:12px; white-space:nowrap;">
                        <?= substr($u['horario_entrada_1'] ?? '08:00', 0, 5) ?>-<?= substr($u['horario_saida_1'] ?? '12:00', 0, 5) ?><br>
                        <?= substr($u['horario_entrada_2'] ?? '13:00', 0, 5) ?>-<?= substr($u['horario_saida_2'] ?? '18:00', 0, 5) ?>
                    </td>
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