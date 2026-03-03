<?php include __DIR__ . '/../geral/header.php'; ?>

<div class="main-content">
    <h1 class="login-title">Gerenciar Fornecedores</h1>
    <p class="login-subtitle">Quem fornece seus insumos?</p>

    <div class="form-container">
        <h3 class="form-title"><?= isset($fornecedorEdit) ? '✏️ Editar Fornecedor' : '🚛 Novo Fornecedor' ?></h3>
        
        <form action="index.php?rota=salvar_fornecedor" method="POST">
            <input type="hidden" name="id" value="<?= $fornecedorEdit['id'] ?? '' ?>">
            
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Nome / Razão Social</label>
                    <input type="text" name="nome" required value="<?= $fornecedorEdit['nome'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>CNPJ</label>
                    <input type="text" name="cnpj" value="<?= $fornecedorEdit['cnpj'] ?? '' ?>" placeholder="00.000.000/0001-00">
                </div>
                <div class="form-group">
                    <label>Categoria (Ex: Tecido)</label>
                    <input type="text" name="categoria" value="<?= $fornecedorEdit['categoria'] ?? '' ?>">
                </div>
            </div>

            <div class="form-grid-4">
                <div class="form-group">
                    <label>Telefone/Contato</label>
                    <input type="text" name="contato" value="<?= $fornecedorEdit['contato'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $fornecedorEdit['email'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>CEP</label>
                    <input type="text" name="cep" value="<?= $fornecedorEdit['cep'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Endereço Completo</label>
                    <input type="text" name="endereco" value="<?= $fornecedorEdit['endereco'] ?? '' ?>">
                </div>
            </div>
             <div class="form-group">
                <label>Cidade</label>
                <input type="text" name="cidade" value="<?= $fornecedorEdit['cidade'] ?? '' ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-green">Salvar</button>
                <?php if(isset($fornecedorEdit)): ?>
                    <a href="index.php?rota=fornecedores" class="btn btn-red">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Contato / Email</th>
                    <th>Cidade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($fornecedores)): ?>
                    <tr><td colspan="5" class="text-center text-muted">Nenhum fornecedor cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($fornecedores as $f): ?>
                    <tr>
                        <td data-label="Nome" class="font-bold" style="color:#e6b800;"><?= htmlspecialchars($f['nome']) ?></td>
                        <td data-label="Categoria"><span class="tag"><?= htmlspecialchars($f['categoria']) ?></span></td>
                        <td data-label="Contato">
                            <?= htmlspecialchars($f['contato']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($f['email']) ?></small>
                        </td>
                        <td data-label="Cidade"><?= htmlspecialchars($f['cidade']) ?></td>
                        <td data-label="Ações">
                            <div class="action-buttons">
                                <a href="index.php?rota=editar_fornecedor&id=<?= $f['id'] ?>" class="btn btn-blue btn-sm">Editar</a>
                                <a href="index.php?rota=excluir_fornecedor&id=<?= $f['id'] ?>" class="btn btn-red btn-sm" onclick="return confirm('Apagar fornecedor?')">Excluir</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>