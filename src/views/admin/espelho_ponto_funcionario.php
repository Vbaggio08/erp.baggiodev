<?php
// Dados esperados nesta view:
// $funcionarios, $funcionarioId, $mesAno, $funcionarioSelecionado, $apontamentos

if (!function_exists('normalizarUrlFotoPonto')) {
    function normalizarUrlFotoPonto($fotoPath) {
        $fotoPath = trim((string)$fotoPath);
        if ($fotoPath === '') {
            return null;
        }

        if (strpos($fotoPath, 'data:image') === 0 || preg_match('~^https?://~i', $fotoPath)) {
            return $fotoPath;
        }

        $base = defined('BASE_URL') ? BASE_URL : '';

        if (strpos($fotoPath, 'fotos_ponto/') === 0) {
            return $base . 'assets/uploads/comprovantes/' . ltrim($fotoPath, '/');
        }

        if (preg_match('~^\d{4}-\d{2}-\d{2}/~', $fotoPath)) {
            return $base . 'assets/uploads/fotos_ponto/' . ltrim($fotoPath, '/');
        }

        return $base . ltrim($fotoPath, '/');
    }
}
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h3 mb-1">Espelho de Ponto por Funcionario</h1>
                <p class="text-muted mb-0">Selecione o funcionario para visualizar as batidas e abrir as fotos de cada ponto.</p>
            </div>
            <a href="index.php?rota=ponto_todos" class="btn btn-outline-secondary">Voltar para Gerenciar Pontos</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="rota" value="espelho_ponto_funcionario">

                <div class="col-lg-6">
                    <label for="usuario_id" class="form-label">Funcionario</label>
                    <select class="form-select" id="usuario_id" name="usuario_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($funcionarios as $funcionario): ?>
                            <option value="<?php echo (int)$funcionario['id']; ?>" <?php echo $funcionarioId === (int)$funcionario['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($funcionario['nome']); ?>
                                <?php if (!empty($funcionario['departamento'])): ?>
                                    - <?php echo htmlspecialchars($funcionario['departamento']); ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-3">
                    <label for="mes_ano" class="form-label">Mes/Ano</label>
                    <input type="month" class="form-control" id="mes_ano" name="mes_ano" value="<?php echo htmlspecialchars($mesAno); ?>">
                </div>

                <div class="col-lg-3 d-grid">
                    <button type="submit" class="btn btn-primary">Buscar Espelho</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($funcionarioId > 0 && !$funcionarioSelecionado): ?>
        <div class="alert alert-warning">Funcionario nao encontrado.</div>
    <?php endif; ?>

    <?php if ($funcionarioSelecionado): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-body d-flex flex-wrap gap-4">
                <div><strong>Funcionario:</strong> <?php echo htmlspecialchars($funcionarioSelecionado['nome']); ?></div>
                <div><strong>Departamento:</strong> <?php echo htmlspecialchars($funcionarioSelecionado['departamento'] ?? '-'); ?></div>
                <div><strong>Periodo:</strong> <?php echo htmlspecialchars(date('m/Y', strtotime($mesAno . '-01'))); ?></div>
                <div><strong>Registros:</strong> <?php echo count($apontamentos); ?></div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Status</th>
                            <th>E1</th>
                            <th>S1</th>
                            <th>E2</th>
                            <th>S2</th>
                            <th>Fotos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($apontamentos)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Nenhum apontamento encontrado para este periodo.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($apontamentos as $apt): ?>
                                <?php
                                $fotos = [
                                    'Entrada 1' => $apt['foto_entrada_1'] ?? null,
                                    'Saida 1' => $apt['foto_saida_1'] ?? null,
                                    'Entrada 2' => $apt['foto_entrada_2'] ?? null,
                                    'Saida 2' => $apt['foto_saida_2'] ?? null,
                                ];
                                ?>
                                <tr>
                                    <td><strong><?php echo date('d/m/Y', strtotime($apt['data'])); ?></strong></td>
                                    <td>
                                        <?php
                                        $status = (string)($apt['status'] ?? '-');
                                        $statusClass = 'bg-secondary';
                                        if ($status === 'presente') $statusClass = 'bg-success';
                                        if ($status === 'falta') $statusClass = 'bg-danger';
                                        if ($status === 'atestado') $statusClass = 'bg-info';
                                        if ($status === 'incompleta') $statusClass = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($apt['hora_entrada_1'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($apt['hora_saida_1'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($apt['hora_entrada_2'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($apt['hora_saida_2'] ?? '-'); ?></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php
                                            $temFoto = false;
                                            foreach ($fotos as $rotulo => $fotoPath):
                                                $urlFoto = normalizarUrlFotoPonto($fotoPath);
                                                if (!$urlFoto) {
                                                    continue;
                                                }
                                                $temFoto = true;
                                            ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary btn-ver-foto"
                                                    data-foto-url="<?php echo htmlspecialchars($urlFoto); ?>"
                                                    data-foto-label="<?php echo htmlspecialchars($rotulo); ?>"
                                                >
                                                    Ver foto <?php echo htmlspecialchars($rotulo); ?>
                                                </button>
                                            <?php endforeach; ?>

                                            <?php if (!$temFoto): ?>
                                                <span class="text-muted small">Sem foto</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalFotoPonto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFotoPontoTitulo">Foto do ponto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalFotoPontoImg" src="" alt="Foto do ponto" class="img-fluid rounded border" style="max-height: 70vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl = document.getElementById('modalFotoPonto');
    if (!modalEl) {
        return;
    }

    const img = document.getElementById('modalFotoPontoImg');
    const titulo = document.getElementById('modalFotoPontoTitulo');

    document.querySelectorAll('.btn-ver-foto').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const url = btn.getAttribute('data-foto-url') || '';
            const label = btn.getAttribute('data-foto-label') || 'Foto do ponto';
            img.src = url;
            titulo.textContent = label;

            // Bootstrap pode carregar depois desta view; por isso resolvemos a instancia no clique.
            if (window.bootstrap && window.bootstrap.Modal) {
                const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
                return;
            }

            // Fallback simples caso Bootstrap nao esteja disponivel por algum motivo.
            window.open(url, '_blank');
        });
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        img.src = '';
    });
})();
</script>
