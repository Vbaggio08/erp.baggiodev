
<div class="box-relatorio">
    <div class="page-header">
        <h1 class="login-title"><i class="bi bi-person-gear action-icon"></i>Gerenciar Usuários</h1>
    </div>
    
    <div class="panel mb-4">
        <h3 class="widget-title mb-3">
            <i class="bi bi-<?= isset($usuarioEdit) ? 'pencil-square' : 'person-plus' ?> action-icon"></i>
            <?= isset($usuarioEdit) ? 'Editar Usuário' : 'Novo Usuário' ?>
        </h3>
        
        <form action="index.php?rota=salvar_usuario" method="POST">
            <input type="hidden" name="id" value="<?= $usuarioEdit['id'] ?? '' ?>">

            <div style="display:grid; grid-template-columns: repeat(4,1fr); gap:15px; margin-bottom:15px;">
                <div>
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" class="form-control"
                           value="<?= htmlspecialchars($usuarioEdit['nome'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="form-label">Usuário (login)</label>
                    <input type="text" name="username" class="form-control"
                           value="<?= htmlspecialchars($usuarioEdit['username'] ?? '') ?>" placeholder="ex: joao.silva">
                </div>
                <div>
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($usuarioEdit['email'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="form-label">CPF</label>
                    <input type="text" name="cpf" class="form-control"
                           value="<?= htmlspecialchars($usuarioEdit['cpf'] ?? '') ?>" required maxlength="14" placeholder="000.000.000-00">
                </div>
                <div>
                    <label class="form-label">Nível</label>
                    <select name="nivel" class="form-select">
                        <option value="funcionario" <?= (isset($usuarioEdit) && $usuarioEdit['nivel'] == 'funcionario') ? 'selected' : '' ?>>Funcionário</option>
                        <option value="admin" <?= (isset($usuarioEdit) && $usuarioEdit['nivel'] == 'admin') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label class="form-label">Senha <?= isset($usuarioEdit) ? '<small class="text-muted">(deixe em branco p/ manter)</small>' : '' ?></label>
                    <input type="password" name="senha" class="form-control" <?= isset($usuarioEdit) ? '' : 'required' ?>>
                </div>
                <div>
                    <label class="form-label">Confirmar senha <?= isset($usuarioEdit) ? '<small class="text-muted">(obrigatório se alterar)</small>' : '' ?></label>
                    <input type="password" name="confirmar_senha" class="form-control" <?= isset($usuarioEdit) ? '' : 'required' ?>>
                </div>
            </div>

            <!-- Horário de Trabalho -->
            <div class="panel mb-3" style="background: var(--surface-elevated); border-left: 4px solid var(--primary-color);">
                <h4 class="widget-title mb-2" style="font-size:14px;">
                    <i class="bi bi-clock action-icon"></i>Horário de Trabalho
                </h4>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:15px;">
                    <div>
                        <label class="form-label">Entrada 1 (Manhã)</label>
                        <input type="time" name="horario_entrada_1" class="form-control"
                               value="<?= $usuarioEdit['horario_entrada_1'] ?? '08:00' ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Saída 1 (Almoço)</label>
                        <input type="time" name="horario_saida_1" class="form-control"
                               value="<?= $usuarioEdit['horario_saida_1'] ?? '12:00' ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Entrada 2 (Tarde)</label>
                        <input type="time" name="horario_entrada_2" class="form-control"
                               value="<?= $usuarioEdit['horario_entrada_2'] ?? '13:00' ?>" required>
                    </div>
                    <div>
                        <label class="form-label">Saída 2 (Fim)</label>
                        <input type="time" name="horario_saida_2" class="form-control"
                               value="<?= $usuarioEdit['horario_saida_2'] ?? '18:00' ?>" required>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn-green">
                    <i class="bi bi-check2 action-icon"></i>Salvar
                </button>
                <?php if(isset($usuarioEdit)): ?>
                    <a href="index.php?rota=gerenciar_usuarios" class="btn-red text-decoration-none">
                        <i class="bi bi-x-lg action-icon"></i>Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-shell">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Usuário</th>
                    <th>E-mail</th>
                    <th>CPF</th>
                    <th>Nível</th>
                    <th>Horário</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($u['nome']) ?></td>
                        <td>
                            <?php if (!empty($u['username'])): ?>
                                <?= htmlspecialchars($u['username']) ?>
                            <?php else: ?>
                                <span class="status-badge status-baixo">PENDENTE</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php if (!empty($u['cpf'])): ?>
                                <?= htmlspecialchars($u['cpf']) ?>
                            <?php else: ?>
                                <span class="status-badge status-baixo">PENDENTE</span>
                            <?php endif; ?>
                        </td>
                        <td><?= strtoupper($u['nivel']) ?></td>
                        <td class="small text-muted" style="white-space:nowrap;">
                            <?= substr($u['horario_entrada_1'] ?? '08:00', 0, 5) ?>&#8209;<?= substr($u['horario_saida_1'] ?? '12:00', 0, 5) ?><br>
                            <?= substr($u['horario_entrada_2'] ?? '13:00', 0, 5) ?>&#8209;<?= substr($u['horario_saida_2'] ?? '18:00', 0, 5) ?>
                        </td>
                        <td>
                            <a href="index.php?rota=editar_usuario&id=<?= $u['id'] ?>"
                               class="text-decoration-none me-2" style="color: var(--primary-color);">
                                <i class="bi bi-pencil-square"></i> Editar
                            </a>
                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                                <a href="index.php?rota=excluir_usuario&id=<?= $u['id'] ?>"
                                   class="text-decoration-none" style="color: var(--brand-red);"
                                   onclick="return confirm('Excluir?')">
                                    <i class="bi bi-trash3"></i> Excluir
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>