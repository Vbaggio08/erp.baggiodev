<?php
include 'conexao.php';

$pageTitle = 'Listagem de Notas';
include 'includes/header.php';

$sucesso = isset($_GET['sucesso']) ? $_GET['sucesso'] : '';

$sqlResumo = "SELECT
    COUNT(*) AS total_lancamentos,
    MAX((nota1 + nota2 + nota3) / 3) AS maior_media,
    MIN((nota1 + nota2 + nota3) / 3) AS menor_media,
    AVG((nota1 + nota2 + nota3) / 3) AS media_geral
FROM notas_alunos";

$resResumo = mysqli_query($conn, $sqlResumo);
$resumo = mysqli_fetch_assoc($resResumo);

$sqlLista = "SELECT
    n.id, u.nome AS aluno_nome, n.bimestre, n.nota1, n.nota2, n.nota3,
    n.peso, n.faltas,
    (n.nota1 + n.nota2 + n.nota3) AS soma_notas,
    ((n.nota1 + n.nota2 + n.nota3) / 3) AS media_simples,
    (((n.nota1 + n.nota2 + n.nota3) / 3) * n.peso) AS media_ponderada
FROM notas_alunos n
INNER JOIN usuarios u ON u.id = n.aluno_id
ORDER BY n.id DESC";

$resLista = mysqli_query($conn, $sqlLista);
?>

<div class="d-flex align-items-center justify-content-between flex-wrap page-header">
    <h2 class="mb-0">Notas dos Alunos</h2>
    <a href="notas_form.php" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i>Novo lançamento</a>
</div>

<?php if ($sucesso !== ''): ?>
    <div class="alert alert-success" role="alert">
        Lançamento salvo com sucesso.
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted d-block">Total de lançamentos</small>
                <strong class="fs-4"><?php echo (int)($resumo['total_lancamentos'] ?? 0); ?></strong>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted d-block">Maior média</small>
                <strong class="fs-4"><?php echo number_format((float)($resumo['maior_media'] ?? 0), 2, ',', '.'); ?></strong>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted d-block">Menor média</small>
                <strong class="fs-4"><?php echo number_format((float)($resumo['menor_media'] ?? 0), 2, ',', '.'); ?></strong>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted d-block">Média geral</small>
                <strong class="fs-4"><?php echo number_format((float)($resumo['media_geral'] ?? 0), 2, ',', '.'); ?></strong>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>Aluno</th>
                <th>Bimestre</th>
                <th>Nota 1</th>
                <th>Nota 2</th>
                <th>Nota 3</th>
                <th class="d-none d-md-table-cell">Soma</th>
                <th>Média simples</th>
                <th class="d-none d-lg-table-cell">Média ponderada</th>
                <th class="d-none d-lg-table-cell">Diferença meta 7</th>
                <th class="d-none d-md-table-cell">Faltas</th>
                <th>Situação</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resLista && mysqli_num_rows($resLista) > 0): ?>
                <?php while ($r = mysqli_fetch_assoc($resLista)): ?>
                    <?php
                    $mediaSimples = (float)$r['media_simples'];
                    $diferencaMeta = max(0, 7.0 - $mediaSimples);

                    if ($mediaSimples >= 7.0 && (int)$r['faltas'] <= 10) {
                        $situacao = 'Aprovado';
                        $classe = 'success';
                    } elseif ($mediaSimples >= 5.0 && $mediaSimples < 7.0 && (int)$r['faltas'] <= 10) {
                        $situacao = 'Recuperação';
                        $classe = 'warning';
                    } else {
                        $situacao = 'Reprovado';
                        $classe = 'danger';
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['aluno_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($r['bimestre'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo number_format((float)$r['nota1'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format((float)$r['nota2'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format((float)$r['nota3'], 2, ',', '.'); ?></td>
                        <td class="d-none d-md-table-cell"><?php echo number_format((float)$r['soma_notas'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format((float)$r['media_simples'], 2, ',', '.'); ?></td>
                        <td class="d-none d-lg-table-cell"><?php echo number_format((float)$r['media_ponderada'], 2, ',', '.'); ?></td>
                        <td class="d-none d-lg-table-cell"><?php echo number_format($diferencaMeta, 2, ',', '.'); ?></td>
                        <td class="d-none d-md-table-cell"><?php echo (int)$r['faltas']; ?></td>
                        <td><span class="badge bg-<?php echo $classe; ?>"><?php echo $situacao; ?></span></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" class="text-center text-muted">Nenhum lançamento encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
