<?php
// Dados disponíveis:
// $apontamento - apontamento de hoje (ou null)
// $config - configuração global
?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5 text-center">
                    
                    <!-- Cabeçalho -->
                    <h1 class="mb-1">⏱️ Bater Ponto</h1>
                    <p class="text-muted mb-4">Registre sua entrada/saída</p>
                    
                    <!-- Status Online/Offline -->
                    <div class="alert alert-info mb-4" id="status-box">
                        <span id="status-icon">🟢</span>
                        <span id="status-text"> ONLINE</span>
                    </div>
                    
                    <!-- Informações de hoje -->
                    <?php if ($apontamento): ?>
                        <div class="bg-light p-3 rounded mb-4">
                            <h5 class="card-title">Hoje (<?php echo date('d/m/Y'); ?>)</h5>
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted">Entrada 1</small>
                                    <p class="fw-bold"><?php echo $apontamento['hora_entrada_1'] ?? '---'; ?></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Saída 1</small>
                                    <p class="fw-bold"><?php echo $apontamento['hora_saida_1'] ?? '---'; ?></p>
                                </div>
                            </div>
                            
                            <?php if ($config['quantidade_batidas'] >= 4): ?>
                                <div class="row text-center mt-3">
                                    <div class="col-6">
                                        <small class="text-muted">Entrada 2</small>
                                        <p class="fw-bold"><?php echo $apontamento['hora_entrada_2'] ?? '---'; ?></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Saída 2</small>
                                        <p class="fw-bold"><?php echo $apontamento['hora_saida_2'] ?? '---'; ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Nenhum ponto registrado hoje
                        </div>
                    <?php endif; ?>
                    
                    <!-- Botão Principal -->
                    <button id="btn-bater-ponto" class="btn btn-success btn-lg w-100 mb-3" style="font-size: 24px; padding: 20px;">
                        ✅ BATER PONTO
                    </button>
                    
                    <!-- Indicador de Sincronização -->
                    <div id="sync-indicator" class="text-muted small d-none">
                        <spinner></spinner> <span id="sync-text">Sincronizando...</span>
                    </div>
                    
                </div>
            </div>
            
            <!-- Links rápidos -->
            <div class="mt-4 text-center">
                <a href="index.php?rota=meu_ponto" class="btn btn-outline-primary btn-sm">📋 Meu Ponto</a>
                <a href="index.php?rota=saldo_horas" class="btn btn-outline-info btn-sm">⏳ Saldo de Horas</a>
                <a href="index.php?rota=solicitar_alteracao_ponto" class="btn btn-outline-warning btn-sm">✏️ Solicitar Alteração</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Batida Próxima (Validação) -->
<div class="modal fade" id="modal-validacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">⚠️ Batida Muito Próxima</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>A última batida foi registrada há <strong id="minutos-decorridos"></strong> minutos.</p>
                <p class="text-muted">Última batida: <strong id="ultima-batida"></strong></p>
                <p>O que deseja fazer?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">❌ Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-saida">✅ Confirmar Saída</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btn-bater-ponto').addEventListener('click', function() {
    baterPonto();
});

function baterPonto() {
    // TODO: Implementar lógica de batida
    alert('Funcionalidade será implementada');
}
</script>
