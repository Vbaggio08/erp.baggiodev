<?php
// View para listar atestados do funcionário
?>

<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-4">📋 Meus Atestados</h1>
        </div>
    </div>
    
    <!-- Cards de Status -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Pendentes</h5>
                    <p class="fs-4 fw-bold">
                        <?php 
                            echo count(array_filter($meus_atestados, function($a) { 
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
                            echo count(array_filter($meus_atestados, function($a) { 
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
                            echo count(array_filter($meus_atestados, function($a) { 
                                return $a['status'] === 'rejeitado'; 
                            })); 
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botão Novo Atestado -->
    <div class="mb-4">
        <a href="index.php?rota=solicitar_atestado" class="btn btn-primary btn-lg">
            ➕ Novo Atestado
        </a>
    </div>
    
    <!-- Abas de Visualização -->
    <div class="card shadow">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-todos" data-bs-toggle="tab" href="#abas-todos" role="tab">
                        📊 Todos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-pendentes" data-bs-toggle="tab" href="#abas-pendentes" role="tab">
                        ⏳ Pendentes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-aprovados" data-bs-toggle="tab" href="#abas-aprovados" role="tab">
                        ✅ Aprovados
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="tab-content">
            <!-- Aba: Todos -->
            <div class="tab-pane fade show active" id="abas-todos" role="tabpanel">
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($meus_atestados as $atestado): ?>
                            <div class="col-lg-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">🏥 <?php echo $atestado['tipo_afastamento']; ?></h6>
                                        <span class="badge badge-<?php 
                                            echo $atestado['status'] === 'pendente' ? 'warning' : 
                                                 ($atestado['status'] === 'aprovado' ? 'success' : 'danger'); 
                                        ?>">
                                            <?php echo strtoupper($atestado['status']); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>📅 Período:</strong><br> <?php echo date('d/m/Y', strtotime($atestado['data_inicio'])); ?> até <?php echo date('d/m/Y', strtotime($atestado['data_fim'])); ?></p>
                                        <p><strong>⏱️ Dias:</strong> 
                                            <?php 
                                                $inicio = new DateTime($atestado['data_inicio']);
                                                $fim = new DateTime($atestado['data_fim']);
                                                $dias = $fim->diff($inicio)->days + 1;
                                                echo $dias;
                                            ?>
                                        </p>
                                        <p><strong>📝 Solicitado em:</strong> <?php echo date('d/m/Y H:i', strtotime($atestado['data_criacao'])); ?></p>
                                        
                                        <?php if ($atestado['status'] === 'rejeitado' && $atestado['motivo_rejeicao']): ?>
                                            <div class="alert alert-danger small">
                                                <strong>❌ Motivo da Rejeição:</strong><br>
                                                <?php echo $atestado['motivo_rejeicao']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-light d-flex gap-2">
                                        <?php if (file_exists('assets/uploads/atestados/' . $atestado['arquivo_comprovante'])): ?>
                                            <a href="assets/uploads/atestados/<?php echo $atestado['arquivo_comprovante']; ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-info flex-grow-1">
                                                👁️ Ver Arquivo
                                            </a>
                                        <?php endif; ?>
                                        <a href="index.php?rota=detalhes_atestado&id=<?php echo $atestado['id']; ?>" 
                                           class="btn btn-sm btn-secondary flex-grow-1">
                                            📋 Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (empty($meus_atestados)): ?>
                        <div class="alert alert-info text-center">
                            <p>Você não possui atestados registrados ainda.</p>
                            <a href="index.php?rota=solicitar_atestado" class="btn btn-primary btn-sm">
                                ➕ Enviar Novo Atestado
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Aba: Pendentes -->
            <div class="tab-pane fade" id="abas-pendentes" role="tabpanel">
                <div class="card-body">
                    <?php 
                        $atestados_pendentes = array_filter($meus_atestados, function($a) { 
                            return $a['status'] === 'pendente'; 
                        });
                    ?>
                    
                    <?php if (!empty($atestados_pendentes)): ?>
                        <div class="row">
                            <?php foreach ($atestados_pendentes as $atestado): ?>
                                <div class="col-lg-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>🏥 <?php echo $atestado['tipo_afastamento']; ?></h6>
                                            <p class="small text-muted">📅 <?php echo date('d/m/Y', strtotime($atestado['data_inicio'])); ?> até <?php echo date('d/m/Y', strtotime($atestado['data_fim'])); ?></p>
                                            <div class="alert alert-warning small">
                                                ⏳ <strong>Em análise pelo RH</strong> - Você será notificado em até 24 horas
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">✅ Nenhum atestado pendente</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Aba: Aprovados -->
            <div class="tab-pane fade" id="abas-aprovados" role="tabpanel">
                <div class="card-body">
                    <?php 
                        $atestados_aprovados = array_filter($meus_atestados, function($a) { 
                            return $a['status'] === 'aprovado'; 
                        });
                    ?>
                    
                    <?php if (!empty($atestados_aprovados)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Período</th>
                                        <th>Dias</th>
                                        <th>Aprovado em</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($atestados_aprovados as $atestado): ?>
                                        <tr>
                                            <td><?php echo $atestado['tipo_afastamento']; ?></td>
                                            <td>
                                                <?php echo date('d/m', strtotime($atestado['data_inicio'])); ?> a 
                                                <?php echo date('d/m/Y', strtotime($atestado['data_fim'])); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $inicio = new DateTime($atestado['data_inicio']);
                                                    $fim = new DateTime($atestado['data_fim']);
                                                    echo $fim->diff($inicio)->days + 1;
                                                ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($atestado['data_processamento'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Nenhum atestado aprovado</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Informações Úteis -->
    <div class="card shadow mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">ℹ️ Informações Importantes</h5>
        </div>
        <div class="card-body">
            <ul>
                <li><strong>Validade:</strong> Atestados devem ser enviados em até 5 dias após o afastamento</li>
                <li><strong>Formatos:</strong> PDF, JPG ou PNG (máximo 5MB)</li>
                <li><strong>Processamento:</strong> Análise realizada em até 24 horas úteis</li>
                <li><strong>Histórico:</strong> Todos os atestados ficam registrados em seu dossiê RH</li>
                <li><strong>Conformidade Legal:</strong> Sistema segue normas CLT e Lei 8.213/1991</li>
            </ul>
        </div>
    </div>
</div>
