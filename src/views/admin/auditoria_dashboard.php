<?php
// Dashboard de auditoria - últimas alterações
?>

<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-4">🔍 Dashboard de Auditoria</h1>
            <p class="text-muted">Últimas alterações registradas no sistema</p>
        </div>
    </div>
    
    <!-- Resumo -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Alterações Hoje</h6>
                    <p class="fs-4 fw-bold">
                        <?php 
                            echo count(array_filter($alteracoes, function($a) {
                                return date('Y-m-d', strtotime($a['criado_em'])) === date('Y-m-d');
                            }));
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Alterações Esta Semana</h6>
                    <p class="fs-4 fw-bold">
                        <?php 
                            $semana_passada = date('Y-m-d', strtotime('-7 days'));
                            echo count(array_filter($alteracoes, function($a) use ($semana_passada) {
                                return strtotime($a['criado_em']) >= strtotime($semana_passada);
                            }));
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Usuários Alteradores</h6>
                    <p class="fs-4 fw-bold">
                        <?php 
                            echo count(array_unique(array_column($alteracoes, 'usuario_alterador_id')));
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="rota" value="auditoria_dashboard">
                
                <div class="col-md-4">
                    <label class="form-label">Tipo de Alteração</label>
                    <select name="tipo_alteracao" class="form-select">
                        <option value="">Todas</option>
                        <option value="entrada_registrada" <?php echo ($_GET['tipo_alteracao'] ?? '') === 'entrada_registrada' ? 'selected' : ''; ?>>Entrada Registrada</option>
                        <option value="saida_registrada" <?php echo ($_GET['tipo_alteracao'] ?? '') === 'saida_registrada' ? 'selected' : ''; ?>>Saída Registrada</option>
                        <option value="ponto_editado" <?php echo ($_GET['tipo_alteracao'] ?? '') === 'ponto_editado' ? 'selected' : ''; ?>>Ponto Editado</option>
                        <option value="foto_capturada" <?php echo ($_GET['tipo_alteracao'] ?? '') === 'foto_capturada' ? 'selected' : ''; ?>>Foto Capturada</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Período</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?php echo $_GET['data_inicio'] ?? ''; ?>">
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">🔍 Filtrar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Timeline de Alterações -->
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📋 Histórico (Últimas 50)</h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                <?php foreach ($alteracoes as $alt): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="card-title">
                                        <?php echo match($alt['tipo_alteracao']) {
                                            'entrada_registrada' => '🔵 Entrada',
                                            'saida_registrada' => '🔴 Saída',
                                            'ponto_editado' => '✏️ Edição',
                                            'foto_capturada' => '📷 Foto',
                                            default => $alt['tipo_alteracao']
                                        }; ?>
                                    </h6>
                                    <p class="small text-muted mb-2">
                                        <strong>Apontamento:</strong> #<?php echo $alt['apontamento_id']; ?> | 
                                        <strong>Usuário:</strong> <?php echo $alt['usuario_nome']; ?>
                                    </p>
                                    <p class="small mb-0">
                                        <code><?php echo htmlspecialchars((string)($alt['motivo_alteracao'] ?? 'Sem motivo informado')); ?></code>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <p class="small text-muted">
                                        🕒 <?php echo date('d/m/Y H:i:s', strtotime($alt['criado_em'])); ?>
                                    </p>
                                    <p class="small">👤 <?php echo !empty($alt['usuario_nome']) ? htmlspecialchars($alt['usuario_nome']) : 'Sistema'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Ações Suspeitas -->
    <?php 
        $suspeitas = array_filter($alteracoes, function($a) {
            return (
                stripos((string)$a['tipo_alteracao'], 'edit') !== false ||
                stripos((string)$a['tipo_alteracao'], 'pendente') !== false
            ) && strtotime($a['criado_em']) > strtotime('-1 hour');
        });
    ?>
    <?php if (!empty($suspeitas)): ?>
        <div class="card shadow mt-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">⚠️ Ações Potencialmente Suspeitas</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($suspeitas as $s): ?>
                    <a href="index.php?rota=auditoria_apontamento&id=<?php echo $s['apontamento_id']; ?>" class="list-group-item list-group-item-action">
                        🚨 <?php echo htmlspecialchars((string)$s['usuario_nome']); ?> - <?php echo htmlspecialchars((string)$s['tipo_alteracao']); ?> em <?php echo date('d/m/Y H:i:s', strtotime($s['criado_em'])); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .timeline {
        border-left: 3px solid #007bff;
        padding-left: 20px;
    }
    .card { border-left: 4px solid #007bff; }
</style>
