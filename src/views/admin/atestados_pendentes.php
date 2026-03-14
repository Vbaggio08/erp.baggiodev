<?php
// Dados disponíveis:
// $atestados - array com atestados pendentes
?>

<div class="container-fluid py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">📋 Atestados Pendentes de Aprovação</h1>
            
            <!-- Cards de Status -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Pendentes</h5>
                            <p class="fs-4 fw-bold">
                                <?php 
                                    echo count(array_filter($atestados, function($a) { 
                                        return $a['status'] === 'pendente'; 
                                    })); 
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Aprovados</h5>
                            <p class="fs-4 fw-bold">
                                <?php 
                                    echo count(array_filter($atestados, function($a) { 
                                        return $a['status'] === 'aprovado'; 
                                    })); 
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Rejeitados</h5>
                            <p class="fs-4 fw-bold">
                                <?php 
                                    echo count(array_filter($atestados, function($a) { 
                                        return $a['status'] === 'rejeitado'; 
                                    })); 
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filtro -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="rota" value="atestados_pendentes">
                        
                        <div class="col-md-6">
                            <label class="form-label">Filtrar por Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="pendente" <?php echo ($_GET['status'] ?? '') === 'pendente' ? 'selected' : ''; ?>>Pendentes</option>
                                <option value="aprovado" <?php echo ($_GET['status'] ?? '') === 'aprovado' ? 'selected' : ''; ?>>Aprovados</option>
                                <option value="rejeitado" <?php echo ($_GET['status'] ?? '') === 'rejeitado' ? 'selected' : ''; ?>>Rejeitados</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Período</label>
                            <input type="date" name="data_inicio" class="form-control" value="<?php echo $_GET['data_inicio'] ?? ''; ?>">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lista de Atestados -->
    <div class="row">
        <?php foreach ($atestados as $atestado): ?>
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">👤 <?php echo $atestado['nome']; ?></h5>
                            <span class="badge badge-<?php 
                                echo $atestado['status'] === 'pendente' ? 'warning' : 
                                     ($atestado['status'] === 'aprovado' ? 'success' : 'danger'); 
                            ?>">
                                <?php echo strtoupper($atestado['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p><strong>📅 Data do Atestado:</strong> <?php echo date('d/m/Y', strtotime($atestado['data_atestado'])); ?></p>
                        <p><strong>🩺 Tipo de Afastamento:</strong> <?php echo $atestado['tipo_afastamento']; ?></p>
                        <p><strong>⏱️ Período:</strong> <?php echo date('d/m/Y', strtotime($atestado['data_inicio'])); ?> à <?php echo date('d/m/Y', strtotime($atestado['data_fim'])); ?></p>
                        <p><strong>📝 Dias:</strong> <?php 
                            $inicio = new DateTime($atestado['data_inicio']);
                            $fim = new DateTime($atestado['data_fim']);
                            $dias = $fim->diff($inicio)->days + 1;
                            echo $dias;
                        ?></p>
                        
                        <?php if ($atestado['observacoes']): ?>
                            <p><strong>📌 Observações:</strong> <?php echo $atestado['observacoes']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Comprovante -->
                    <div class="card-footer bg-light">
                        <p class="mb-2"><strong>📎 Comprovante:</strong></p>
                        <?php if (file_exists('assets/uploads/atestados/' . $atestado['arquivo_comprovante'])): ?>
                            <a href="assets/uploads/atestados/<?php echo $atestado['arquivo_comprovante']; ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-info me-2">
                                👁️ Visualizar
                            </a>
                        <?php else: ?>
                            <span class="badge bg-danger">Arquivo não encontrado</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Ações (se pendente) -->
                    <?php if ($atestado['status'] === 'pendente'): ?>
                        <div class="card-footer">
                            <div class="row g-2">
                                <div class="col-6">
                                    <form method="POST" action="index.php?rota=aprovar_atestado" style="display:inline;">
                                        <input type="hidden" name="atestado_id" value="<?php echo $atestado['id']; ?>">
                                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Aprovar este atestado?')">
                                            ✅ Aprovar
                                        </button>
                                    </form>
                                </div>
                                <div class="col-6">
                                    <button type="button" 
                                            class="btn btn-danger w-100" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modal-rejeitar-<?php echo $atestado['id']; ?>">
                                        ❌ Rejeitar
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal de Rejeição -->
                        <div class="modal fade" id="modal-rejeitar-<?php echo $atestado['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Motivo da Rejeição</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="index.php?rota=rejeitar_atestado">
                                        <div class="modal-body">
                                            <input type="hidden" name="atestado_id" value="<?php echo $atestado['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Motivo da Rejeição</label>
                                                <textarea name="motivo_rejeicao" 
                                                          class="form-control" 
                                                          rows="4" 
                                                          required
                                                          placeholder="Descreva o motivo..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-danger">Confirmar Rejeição</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card-footer text-muted">
                            <strong>Processado:</strong> <?php echo date('d/m/Y H:i', strtotime($atestado['data_processamento'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Se não há atestados -->
    <?php if (empty($atestados)): ?>
        <div class="alert alert-info text-center">
            <h4>✅ Nenhum atestado pendente</h4>
            <p>Todos os atestados foram processados!</p>
        </div>
    <?php endif; ?>
</div>
