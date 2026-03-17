<?php
// $solicitacoes: solicitações pendentes para aprovação/rejeição
$msg = $_GET['msg'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="mb-1">Autorizar Alterações de Ponto</h2>
            <p class="text-muted mb-0">Aprovação obrigatória para alterações pendentes, incluindo autoedição de admin/RH.</p>
        </div>
    </div>

    <?php if ($msg === 'aprovada'): ?>
        <div class="alert alert-success">Solicitação aprovada e aplicada com sucesso.</div>
    <?php elseif ($msg === 'rejeitada'): ?>
        <div class="alert alert-warning">Solicitação rejeitada.</div>
    <?php elseif ($msg === 'autoaprovacao_bloqueada'): ?>
        <div class="alert alert-danger">Autoaprovação bloqueada. Outro admin/RH deve aprovar.</div>
    <?php elseif ($msg === 'autorizacao_pendente'): ?>
        <div class="alert alert-info">Autorização pendente: esta alteração precisa ser aprovada por outro admin/RH.</div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($solicitacoes)): ?>
                <div class="text-center text-muted py-4">Nenhuma solicitação pendente.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Funcionário</th>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Correção</th>
                                <th>Motivo</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitacoes as $s): ?>
                                <tr>
                                    <td>#<?php echo (int)$s['id']; ?></td>
                                    <td><?php echo htmlspecialchars($s['usuario_nome']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($s['data_apontamento'])); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($s['tipo_alteracao']); ?></span></td>
                                    <td>
                                        E1: <?php echo $s['entrada_1_corrigida'] ?: '-'; ?>
                                        | S1: <?php echo $s['saida_1_corrigida'] ?: '-'; ?>
                                        | E2: <?php echo $s['entrada_2_corrigida'] ?: '-'; ?>
                                        | S2: <?php echo $s['saida_2_corrigida'] ?: '-'; ?>
                                    </td>
                                    <td><?php echo nl2br(htmlspecialchars((string)$s['motivo'])); ?></td>
                                    <td>
                                        <form method="POST" action="index.php?rota=aprovar_solicitacao_ponto" class="mb-1">
                                            <input type="hidden" name="solicitacao_id" value="<?php echo (int)$s['id']; ?>">
                                            <input type="text" name="observacao" class="form-control form-control-sm mb-1" placeholder="Observação (opcional)">
                                            <button type="submit" class="btn btn-sm btn-success w-100">Aprovar</button>
                                        </form>
                                        <form method="POST" action="index.php?rota=rejeitar_solicitacao_ponto">
                                            <input type="hidden" name="solicitacao_id" value="<?php echo (int)$s['id']; ?>">
                                            <input type="text" name="observacao" class="form-control form-control-sm mb-1" placeholder="Motivo da rejeição">
                                            <button type="submit" class="btn btn-sm btn-danger w-100">Rejeitar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
