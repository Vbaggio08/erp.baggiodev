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
                                return date('Y-m-d', strtotime($a['data_alteracao'])) === date('Y-m-d');
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
                                return strtotime($a['data_alteracao']) >= strtotime($semana_passada);
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
                                        <code><?php echo $alt['detalhes']; ?></code>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <p class="small text-muted">
                                        🕒 <?php echo date('d/m/Y H:i:s', strtotime($alt['data_alteracao'])); ?>
                                    </p>
                                    <p class="small">
                                        📍 IP: <?php echo $alt['ip_origem']; ?>
                                    </p>
                                    <?php if ($alt['usuario_alterador_id'] !== $alt['usuario_id']): ?>
                                        <p class="small text-warning">
                                            ⚠️ <strong>Alterado por RH:</strong> <?php echo $alt['usuario_alterador_nome']; ?>
                                        </p>
                                    <?php endif; ?>
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
            // Flagear: mesma pessoa editando seus próprios pontos múltiplas vezes em curto tempo, etc
            return $a['usuario_alterador_id'] !== $a['usuario_id'] || 
                   ($a['tipo_alteracao'] === 'ponto_editado' && strtotime($a['data_alteracao']) > strtotime('-1 hour'));
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
                        🚨 <?php echo $s['usuario_nome']; ?> - <?php echo $s['tipo_alteracao']; ?> em <?php echo strtotime($s['data_alteracao']); ?>
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
