<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="main-content">
    <h1 class="login-title">Gerenciar Clientes</h1>

    <div class="form-container">
        <h3 class="form-title"><?= isset($clienteEdit) ? '✏️ Editar Cliente' : '👤 Novo Cliente' ?></h3>
        
        <form action="index.php?rota=salvar_cliente" method="POST">
            <input type="hidden" name="id" value="<?= $clienteEdit['id'] ?? '' ?>">
            
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Nome / Razão Social</label>
                    <input type="text" name="nome" required value="<?= $clienteEdit['nome'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>CPF / CNPJ</label>
                    <input type="text" name="cpf_cnpj" value="<?= $clienteEdit['cpf_cnpj'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" value="<?= $clienteEdit['telefone'] ?? '' ?>">
                </div>
            </div>

            <div class="form-grid-4">
                 <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $clienteEdit['email'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>CEP</label>
                    <input type="text" name="cep" value="<?= $clienteEdit['cep'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Endereço Completo</label>
                    <input type="text" name="endereco" value="<?= $clienteEdit['endereco'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" name="cidade" value="<?= $clienteEdit['cidade'] ?? '' ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-green">Salvar</button>
                <?php if(isset($clienteEdit)): ?>
                    <a href="index.php?rota=clientes" class="btn btn-red">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone / Email</th>
                    <th>Cidade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td data-label="Nome" class="font-bold"><?= htmlspecialchars($c['nome']) ?></td>
                    <td data-label="Contato">
                        <?= htmlspecialchars($c['telefone'] ?? '-') ?><br>
                        <small class="text-muted"><?= htmlspecialchars($c['email'] ?? '-') ?></small>
                    </td>
                    <td data-label="Cidade"><?= htmlspecialchars($c['cidade'] ?? '-') ?></td>
                    <td data-label="Ações">
                        <div class="action-buttons">
                            <a href="index.php?rota=editar_cliente&id=<?= $c['id'] ?>" class="btn btn-blue btn-sm">Editar</a>
                            <a href="index.php?rota=excluir_cliente&id=<?= $c['id'] ?>" class="btn btn-red btn-sm" onclick="return confirm('Tem certeza que deseja excluir este cliente?')">Excluir</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>