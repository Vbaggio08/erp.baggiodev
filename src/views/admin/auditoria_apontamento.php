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
                                <span class="badge bg-<?php 
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
                        <div class="col-12 mt-3">
                            <p><strong>Fotos do Apontamento:</strong></p>
                            <div class="d-flex flex-wrap gap-2">
                                <?php
                                $fotos = [
                                    'Entrada 1' => $apontamento['foto_entrada_1'] ?? null,
                                    'Saída 1' => $apontamento['foto_saida_1'] ?? null,
                                    'Entrada 2' => $apontamento['foto_entrada_2'] ?? null,
                                    'Saída 2' => $apontamento['foto_saida_2'] ?? null,
                                ];
                                foreach ($fotos as $rotulo => $fotoPath):
                                    if (empty($fotoPath)) continue;
                                    $urlFoto = (strpos($fotoPath, 'data:image') === 0)
                                        ? $fotoPath
                                        : (defined('BASE_URL') ? BASE_URL : '') . ltrim($fotoPath, '/');
                                ?>
                                    <a href="<?php echo htmlspecialchars($urlFoto); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                        <?php echo $rotulo; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
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
                                                <?php echo date('d/m/Y H:i:s', strtotime($evento['criado_em'])); ?>
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <p class="small mb-2">
                                                <strong>Responsável:</strong> 
                                                <?php echo !empty($evento['usuario_nome']) ? $evento['usuario_nome'] : 'Sistema'; ?>
                                            </p>
                                            
                                            <!-- Detalhes da Alteração -->
                                            <p class="small mb-2"><strong>Motivo:</strong> <?php echo !empty($evento['motivo_alteracao']) ? htmlspecialchars($evento['motivo_alteracao']) : 'Não informado'; ?></p>
                                            <div class="small text-muted mb-2"><strong>Valor anterior:</strong> <code><?php echo htmlspecialchars((string)($evento['valor_anterior'] ?? '')); ?></code></div>
                                            <div class="small text-muted"><strong>Valor novo:</strong> <code><?php echo htmlspecialchars((string)($evento['valor_novo'] ?? '')); ?></code></div>
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
        background: var(--surface-color);
        border: 3px solid var(--primary-color);
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
        background: #444;
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
                        <code>ID auditoria: ${data.id}</code>
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
