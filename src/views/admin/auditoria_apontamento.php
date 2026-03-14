<?php
// Histórico completo de um apontamento específico
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="mb-4">🔍 Histórico Completo de Apontamento</h1>
            
            <!-- Info do Apontamento -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📋 Apontamento #<?php echo $apontamento['id']; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Funcionário:</strong> <?php echo $apontamento['usuario_nome']; ?></p>
                            <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($apontamento['data'])); ?></p>
                            <p><strong>Status Atual:</strong> 
                                <span class="badge badge-<?php 
                                    echo $apontamento['status'] === 'presente' ? 'success' : 
                                         ($apontamento['status'] === 'falta' ? 'danger' : 'info');
                                ?>">
                                    <?php echo strtoupper($apontamento['status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Horários Atuais:</strong></p>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Entrada 1:</strong></td>
                                    <td><?php echo $apontamento['hora_entrada_1'] ?? '---'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Saída 1:</strong></td>
                                    <td><?php echo $apontamento['hora_saida_1'] ?? '---'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Entrada 2:</strong></td>
                                    <td><?php echo $apontamento['hora_entrada_2'] ?? '---'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Saída 2:</strong></td>
                                    <td><?php echo $apontamento['hora_saida_2'] ?? '---'; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Timeline de Alterações -->
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">📜 Histórico de Alterações (<?php echo count($historico); ?> eventos)</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($historico as $idx => $evento): ?>
                            <div class="event mb-4">
                                <div class="event-marker">
                                    <span class="event-icon">
                                        <?php 
                                            echo match($evento['tipo_alteracao']) {
                                                'entrada_registrada' => '🟢',
                                                'saida_registrada' => '🔴',
                                                'ponto_editado' => '✏️',
                                                'foto_capturada' => '📷',
                                                default => '📌'
                                            };
                                        ?>
                                    </span>
                                </div>
                                <div class="event-content">
                                    <div class="card">
                                        <div class="card-header">
                                            <strong>
                                                <?php 
                                                    echo match($evento['tipo_alteracao']) {
                                                        'entrada_registrada' => 'Entrada Registrada',
                                                        'saida_registrada' => 'Saída Registrada',
                                                        'ponto_editado' => 'Ponto Editado',
                                                        'foto_capturada' => 'Foto Capturada',
                                                        default => $evento['tipo_alteracao']
                                                    };
                                                ?>
                                            </strong>
                                            <span class="float-end text-muted">
                                                <?php echo date('d/m/Y H:i:s', strtotime($evento['data_alteracao'])); ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <p class="small mb-2">
                                                <strong>Responsável:</strong> 
                                                <?php 
                                                    if ($evento['usuario_alterador_id'] === $evento['usuario_id']) {
                                                        echo $evento['usuario_nome'] . ' (próprio usuário)';
                                                    } else {
                                                        echo $evento['usuario_alterador_nome'] . ' (RH/Admin) ⚠️';
                                                    }
                                                ?>
                                            </p>
                                            <p class="small mb-2">
                                                <strong>IP de Origem:</strong> <code><?php echo $evento['ip_origem']; ?></code>
                                            </p>
                                            <p class="small mb-2">
                                                <strong>Geolocalização:</strong> 📍 <?php echo $evento['latitude'] ?? 'Não capturada'; ?>, <?php echo $evento['longitude'] ?? ''; ?>
                                            </p>
                                            
                                            <!-- Detalhes da Alteração -->
                                            <?php if ($evento['tipo_alteracao'] === 'ponto_editado'): ?>
                                                <div class="alert alert-info small">
                                                    <p class="mb-1"><strong>Alterações Realizadas:</strong></p>
                                                    <code><?php echo nl2br($evento['detalhes']); ?></code>
                                                </div>
                                            <?php else: ?>
                                                <p class="small text-muted"><?php echo $evento['detalhes']; ?></p>
                                            <?php endif; ?>
                                            
                                            <!-- Foto Capturada (se existir) -->
                                            <?php if ($evento['tipo_alteracao'] === 'foto_capturada' && isset($evento['foto_path'])): ?>
                                                <p class="small">
                                                    <a href="<?php echo $evento['foto_path']; ?>" target="_blank" class="btn btn-sm btn-info">
                                                        👁️ Ver Foto
                                                    </a>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer bg-light">
                                            <span class="badge bg-secondary"><?php echo $evento['tipo_alteracao']; ?></span>
                                            <span class="badge bg-dark">#<?php echo $idx + 1; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Integridade do Registro -->
            <div class="card shadow mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">🔐 Validação de Integridade</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">Verificar se este apontamento foi alterado de forma não autorizada:</p>
                    <button type="button" class="btn btn-warning" onclick="validarIntegridade(<?php echo $apontamento['id']; ?>)">
                        🔍 Verificar Integridade
                    </button>
                    <div id="resultado-integridade" class="mt-3"></div>
                </div>
            </div>
            
            <!-- Ações -->
            <div class="mt-4">
                <a href="index.php?rota=auditoria_dashboard" class="btn btn-secondary">← Voltar</a>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .event {
        position: relative;
        padding-left: 40px;
    }
    
    .event-marker {
        position: absolute;
        left: -35px;
        top: 0;
        width: 24px;
        height: 24px;
        background: white;
        border: 3px solid #007bff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .event-icon {
        font-size: 18px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: -20px;
        top: 30px;
        bottom: 0;
        width: 3px;
        background: #dee2e6;
    }
</style>

<script>
function validarIntegridade(apontamentoId) {
    fetch(`index.php?rota=validar_integridade&id=${apontamentoId}`)
        .then(response => response.json())
        .then(data => {
            const resultado = document.getElementById('resultado-integridade');
            if (data.valido) {
                resultado.innerHTML = `
                    <div class="alert alert-success">
                        ✅ <strong>Integridade Verificada!</strong>
                        <p>Este apontamento não foi alterado desde sua criação.</p>
                        <code>${data.hash}</code>
                    </div>
                `;
            } else {
                resultado.innerHTML = `
                    <div class="alert alert-danger">
                        ⚠️ <strong>ALERTA: Possível Modificação Detectada!</strong>
                        <p>${data.mensagem}</p>
                    </div>
                `;
            }
        })
        .catch(err => console.error('Erro:', err));
}
</script>
