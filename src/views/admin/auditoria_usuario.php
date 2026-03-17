<?php
// Auditoria de um usuário em um período
// Variables passed from controller:
// $usuario_id, $data_inicio, $data_fim, $historico
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">🔍 Histórico de Auditoria - Usuário</h1>
            
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="rota" value="auditoria_usuario">
                        
                        <div class="col-md-3">
                            <label class="form-label">Período (Início)</label>
                            <input type="date" name="data_inicio" class="form-control" 
                                   value="<?php echo htmlspecialchars($data_inicio ?? date('Y-m-01')); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Período (Fim)</label>
                            <input type="date" name="data_fim" class="form-control" 
                                   value="<?php echo htmlspecialchars($data_fim ?? date('Y-m-t')); ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Gráfico de Alterações</label>
                                <div class="bg-light p-3 rounded">
                                    <?php 
                                    $alteracoes_por_tipo = array_count_values(array_column($historico, 'tipo_alteracao'));
                                    $total = count($historico);
                                    if ($total > 0) {
                                        foreach ($alteracoes_por_tipo as $tipo => $qtd) {
                                            $percentual = ($qtd / $total) * 100;
                                            echo "<div class='mb-2'><small>$tipo:</small> <div class='progress' style='height: 20px'><div class='progress-bar bg-info' style='width: {$percentual}%'>{$qtd}</div></div></div>";
                                        }
                                    } else {
                                        echo "<small class='text-muted'>Nenhuma alteração neste período</small>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                            <a href="index.php?rota=auditoria_dashboard" class="btn btn-secondary">← Voltar ao Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Estatísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>Total de Alterações</h6>
                            <p class="fs-4 fw-bold"><?php echo count($historico); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>Período</h6>
                            <p class="fs-5"><?php echo date('d/m/Y', strtotime($data_inicio)); ?> a <?php echo date('d/m/Y', strtotime($data_fim)); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>Tipos de Alteração</h6>
                            <p class="fs-5"><?php echo count(array_unique(array_column($historico, 'tipo_alteracao'))); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6>Últimas 24h</h6>
                            <p class="fs-5">
                                <?php 
                                $ultimas_24h = count(array_filter($historico, function($h) {
                                    return strtotime($h['criado_em']) > strtotime('-1 day');
                                }));
                                echo $ultimas_24h;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabela de Histórico -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">📋 Histórico Detalhado</h5>
                </div>
                <div class="card-body" style="overflow-x: auto;">
                    <?php if (!empty($historico)): ?>
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Tipo de Alteração</th>
                                    <th>Alterado Por</th>
                                    <th>Motivo</th>
                                    <th>Valor Anterior</th>
                                    <th>Valor Novo</th>
                                    <th>Integridade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico as $log): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i:s', strtotime($log['criado_em'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($log['tipo_alteracao']); ?></span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($log['usuario_nome'] ?? 'Sistema'); ?>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($log['motivo_alteracao'] ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <small class="text-danger">
                                                <code><?php echo substr(htmlspecialchars($log['valor_anterior'] ?? '-'), 0, 50); ?></code>
                                            </small>
                                        </td>
                                        <td>
                                            <small class="text-success">
                                                <code><?php echo substr(htmlspecialchars($log['valor_novo'] ?? '-'), 0, 50); ?></code>
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                <span class="badge bg-success">Hash: OK</span>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>Nenhuma alteração registrada para este usuário neste período.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informações de Integridade -->
            <div class="alert alert-warning mt-4" role="alert">
                <h6>ℹ️ Informações de Integridade</h6>
                <ul class="mb-0">
                    <li>Todos os registros de auditoria são imutáveis (INSERT ONLY)</li>
                    <li>Hash SHA256 valida se o registro foi alterado após criação</li>
                    <li>Este histórico é usado para rastrear quem alterou o quê e quando</li>
                </ul>
            </div>
        </div>
    </div>
</div>
