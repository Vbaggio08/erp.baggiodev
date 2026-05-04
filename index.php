<?php
include 'conexao.php';

$pageTitle = "Usuários Cadastrados";
include 'includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between flex-wrap page-header">
  <h2 class="mb-0">Usuários</h2>

  <a href="form.php" class="btn btn-success">
    <i class="bi bi-plus-circle me-1"></i>Novo usuário
  </a>
</div>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead>
      <tr>
        <th>Nome</th>
        <th>Email</th>
        <th>Telefone</th>
        <th>Idade</th>
        <th>Cidade</th>
        <th>Curso</th>
        <th class="text-center">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $res = mysqli_query($conn, "SELECT * FROM usuarios");
      while ($r = mysqli_fetch_assoc($res)){
      ?>
        <tr>
          <td><?php echo htmlspecialchars($r['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($r['telefone'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo (int)$r['idade']; ?></td>
          <td><?php echo htmlspecialchars($r['cidade'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($r['curso'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td class="text-center">
            <div class="btn-group" role="group" aria-label="Ações do usuário">
              <a
                class="btn btn-sm btn-outline-primary btn-icon"
                href="editar.php?id=<?php echo $r['id']; ?>"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Editar usuário"
                aria-label="Editar usuário"
              >
                <i class="bi bi-pencil"></i>
              </a>
              <a
                class="btn btn-sm btn-outline-danger btn-icon"
                href="deletar.php?id=<?php echo $r['id']; ?>"
                onclick="return confirm('Tem certeza que deseja excluir?')"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Excluir usuário"
                aria-label="Excluir usuário"
              >
                <i class="bi bi-trash3"></i>
              </a>
            </div>
          </td>
        </tr>
      <?php
      }
      ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php' ?>