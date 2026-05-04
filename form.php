<?php
$pageTitle = "Cadastro de um novo usuário";
include 'includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-12 col-lg-8">
    <div class="card shadow-sm border">
      <div class="card-header">
        <h2 class="h5 mb-0"><i class="bi bi-person-plus me-1"></i>Novo usuário</h2>
      </div>
      <div class="card-body">
        <form method="POST" action="salvar.php" class="row g-3">
          <div class="col-12">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control" name="nome" id="nome" required>
          </div>
          <div class="col-12 col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email" required>
          </div>
          <div class="col-12 col-md-6">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" class="form-control" name="telefone" id="telefone" required>
          </div>
          <div class="col-12 col-md-4">
            <label for="idade" class="form-label">Idade</label>
            <input type="number" class="form-control" min="1" name="idade" id="idade" required>
          </div>
          <div class="col-12 col-md-4">
            <label for="cidade" class="form-label">Cidade</label>
            <input type="text" class="form-control" name="cidade" id="cidade" required>
          </div>
          <div class="col-12 col-md-4">
            <label for="curso" class="form-label">Curso</label>
            <input type="text" class="form-control" name="curso" id="curso" required>
          </div>
          <div class="col-12 d-flex flex-wrap gap-2 mt-2">
            <a href="index.php" class="btn btn-outline-secondary">
              <i class="bi bi-x-circle me-1"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2-circle me-1"></i>Salvar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php' ?>
