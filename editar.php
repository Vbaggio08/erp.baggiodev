<?php
include 'conexao.php';

$pageTitle = "Edição de Usuário";
include 'includes/header.php';

$id = $_GET['id'];
$res = mysqli_query($conn, "SELECT * FROM usuarios WHERE id = $id");
$dados = mysqli_fetch_assoc($res);
?>

<div class="row justify-content-center">
  <div class="col-12 col-lg-8">
    <div class="card shadow-sm border">
      <div class="card-header">
        <h2 class="h5 mb-0"><i class="bi bi-pencil-square me-1"></i>Editar usuário</h2>
      </div>
      <div class="card-body">
        <form action="atualizar.php" method="POST" class="row g-3">
          <input type="hidden" name="id" value="<?php echo $dados['id']; ?>">

          <div class="col-12">
            <label class="form-label" for="nome">Nome</label>
            <input class="form-control" type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($dados['nome'], ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($dados['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="telefone">Telefone</label>
            <input class="form-control" type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($dados['telefone'], ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label" for="idade">Idade</label>
            <input class="form-control" type="number" id="idade" name="idade" min="1" value="<?php echo (int)$dados['idade']; ?>" required>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label" for="cidade">Cidade</label>
            <input class="form-control" type="text" id="cidade" name="cidade" value="<?php echo htmlspecialchars($dados['cidade'], ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label" for="curso">Curso</label>
            <input class="form-control" type="text" id="curso" name="curso" value="<?php echo htmlspecialchars($dados['curso'], ENT_QUOTES, 'UTF-8'); ?>" required>
          </div>

          <div class="col-12 d-flex flex-wrap gap-2 mt-2">
            <a href="index.php" class="btn btn-outline-secondary">
              <i class="bi bi-x-circle me-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2-circle me-1"></i>Atualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php' ?>