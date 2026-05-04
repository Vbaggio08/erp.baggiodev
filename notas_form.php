<?php

include 'conexao.php';

$pageTitle = 'Cadastrar Notas';
include 'includes/header.php';

$erro = isset($_GET['erro']) ? $_GET['erro'] : '';
$resAlunos = mysqli_query($conn, "SELECT id, nome FROM usuarios ORDER BY nome ASC");
?>

<div class="row justify-content-center notas-hr">
	<div class="col-12 col-lg-10">
		<div class="card border border-secondary shadow">
			<div class="card-header py-3">
				<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
					<div>
						<h2 class="h4 mb-1"><i class="bi bi-journal-check me-2"></i>Novo lançamento de notas</h2>
						<p class="mb-0 small text-muted">Preencha os campos abaixo para registrar o desempenho do aluno.</p>
					</div>
					<a href="notas_index.php" class="btn btn-outline-secondary btn-sm">
						<i class="bi bi-arrow-left me-1"></i>Voltar
					</a>
				</div>
			</div>

			<div class="card-body p-4">
				<?php if ($erro !== ''): ?>
					<div class="alert alert-danger d-flex align-items-center" role="alert">
						<i class="bi bi-exclamation-triangle-fill me-2"></i>
						<div><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></div>
					</div>
				<?php endif; ?>

				<form action="notas_salvar.php" method="POST" class="row g-4">
					<div class="col-12 col-md-7">
						<label class="form-label fw-semibold" for="aluno_id">Aluno</label>
						<select class="form-select" id="aluno_id" name="aluno_id" required>
							<option value="">Selecione o aluno...</option>
							<?php while ($aluno = mysqli_fetch_assoc($resAlunos)): ?>
								<option value="<?php echo (int)$aluno['id']; ?>">
									<?php echo htmlspecialchars($aluno['nome'], ENT_QUOTES, 'UTF-8'); ?>
								</option>
							<?php endwhile; ?>
						</select>
					</div>

					<div class="col-12 col-md-5">
						<label class="form-label fw-semibold" for="bimestre">Bimestre</label>
						<select class="form-select" id="bimestre" name="bimestre" required>
							<option value="">Selecione o bimestre...</option>
							<option value="1º Bimestre">1º Bimestre</option>
							<option value="2º Bimestre">2º Bimestre</option>
							<option value="3º Bimestre">3º Bimestre</option>
							<option value="4º Bimestre">4º Bimestre</option>
						</select>
					</div>

					<div class="col-12">
						<hr class="my-1">
						<h3 class="h6 text-uppercase text-muted mb-0">Notas e frequência</h3>
					</div>

					<div class="col-12 col-md-4">
						<label class="form-label fw-semibold" for="nota1">Nota 1</label>
						<div class="input-group">
							<span class="input-group-text">N1</span>
							<input class="form-control" type="number" id="nota1" name="nota1" min="0" max="10" step="0.01" required>
						</div>
					</div>

					<div class="col-12 col-md-4">
						<label class="form-label fw-semibold" for="nota2">Nota 2</label>
						<div class="input-group">
							<span class="input-group-text">N2</span>
							<input class="form-control" type="number" id="nota2" name="nota2" min="0" max="10" step="0.01" required>
						</div>
					</div>

					<div class="col-12 col-md-4">
						<label class="form-label fw-semibold" for="nota3">Nota 3</label>
						<div class="input-group">
							<span class="input-group-text">N3</span>
							<input class="form-control" type="number" id="nota3" name="nota3" min="0" max="10" step="0.01" required>
						</div>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label fw-semibold" for="peso">Peso</label>
						<div class="input-group">
							<span class="input-group-text"><i class="bi bi-sliders"></i></span>
							<input class="form-control" type="number" id="peso" name="peso" min="0.01" step="0.01" value="1.00" required>
						</div>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label fw-semibold" for="faltas">Faltas</label>
						<div class="input-group">
							<span class="input-group-text"><i class="bi bi-person-x"></i></span>
							<input class="form-control" type="number" id="faltas" name="faltas" min="0" step="1" value="0" required>
						</div>
					</div>

					<div class="col-12 d-flex flex-wrap gap-2 pt-2">
						<a href="notas_index.php" class="btn btn-outline-secondary">
							<i class="bi bi-x-circle me-1"></i>Cancelar
						</a>
						<button class="btn btn-primary" type="submit">
							<i class="bi bi-check2-circle me-1"></i>Salvar notas
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<?php include 'includes/footer.php'; ?>
