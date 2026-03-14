<?php
// Dados disponíveis:
// $apontamento - array com dados do apontamento a editar
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">✏️ Editar Apontamento</h4>
                </div>
                
                <div class="card-body">
                    <!-- Info do Funcionário -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Funcionário:</strong></p>
                            <p class="text-muted"><?php echo $apontamento['nome']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Data:</strong></p>
                            <p class="text-muted"><?php echo date('d/m/Y', strtotime($apontamento['data'])); ?></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Formulário -->
                    <form id="form-editar-ponto" method="POST">
                        <input type="hidden" name="apontamento_id" value="<?php echo $apontamento['id']; ?>">
                        <input type="hidden" name="usuario_id" value="<?php echo $apontamento['usuario_id']; ?>">
                        
                        <!-- Primeira Batida -->
                        <fieldset class="mb-4">
                            <legend class="h5 mb-3">🕐 Primeira Batida</legend>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Entrada 1</label>
                                    <input type="time" 
                                           name="hora_entrada_1" 
                                           class="form-control" 
                                           value="<?php echo explode(' ', $apontamento['hora_entrada_1'])[1] ?? ''; ?>"
                                           required>
                                    <small class="form-text text-muted">Original: <?php echo $apontamento['hora_entrada_1'] ?? '---'; ?></small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Saída 1</label>
                                    <input type="time" 
                                           name="hora_saida_1" 
                                           class="form-control" 
                                           value="<?php echo explode(' ', $apontamento['hora_saida_1'])[1] ?? ''; ?>">
                                    <small class="form-text text-muted">Original: <?php echo $apontamento['hora_saida_1'] ?? '---'; ?></small>
                                </div>
                            </div>
                        </fieldset>
                        
                        <!-- Segunda Batida -->
                        <fieldset class="mb-4">
                            <legend class="h5 mb-3">🕐 Segunda Batida</legend>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Entrada 2</label>
                                    <input type="time" 
                                           name="hora_entrada_2" 
                                           class="form-control" 
                                           value="<?php echo explode(' ', $apontamento['hora_entrada_2'])[1] ?? ''; ?>">
                                    <small class="form-text text-muted">Original: <?php echo $apontamento['hora_entrada_2'] ?? '---'; ?></small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Saída 2</label>
                                    <input type="time" 
                                           name="hora_saida_2" 
                                           class="form-control" 
                                           value="<?php echo explode(' ', $apontamento['hora_saida_2'])[1] ?? ''; ?>">
                                    <small class="form-text text-muted">Original: <?php echo $apontamento['hora_saida_2'] ?? '---'; ?></small>
                                </div>
                            </div>
                        </fieldset>
                        
                        <!-- Justificativa -->
                        <div class="mb-4">
                            <label class="form-label">Motivo da Alteração</label>
                            <textarea name="motivo_alteracao" 
                                      class="form-control" 
                                      rows="4" 
                                      required
                                      placeholder="Descreva o motivo desta alteração para auditoria..."></textarea>
                            <small class="form-text text-muted">Este campo será registrado na auditoria</small>
                        </div>
                        
                        <!-- Horas Calculadas (somente leitura) -->
                        <div class="alert alert-info">
                            <strong>⏱️ Cálculo de Horas:</strong>
                            <p class="mb-0" id="horas-calculadas">Insira os horários para calcular...</p>
                        </div>
                        
                        <!-- Botões -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                ✅ Salvar Alterações
                            </button>
                            <a href="index.php?rota=ponto_todos" class="btn btn-secondary">
                                ❌ Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Histórico de Alterações -->
            <div class="card shadow mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">📋 Histórico de Alterações</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php 
                            // $alteracoes seria passada do controller com histórico de alterações
                            // foreach ($alteracoes as $alt):
                                // echo "Alteração registrada aqui";
                            // endforeach;
                        ?>
                        <p class="text-muted">Histórico de alterações para este apontamento será exibido aqui.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('form-editar-ponto').addEventListener('change', calcularHoras);

function calcularHoras() {
    const entrada1 = document.querySelector('input[name="hora_entrada_1"]').value;
    const saida1 = document.querySelector('input[name="hora_saida_1"]').value;
    const entrada2 = document.querySelector('input[name="hora_entrada_2"]').value;
    const saida2 = document.querySelector('input[name="hora_saida_2"]').value;
    
    let totalHoras = 0;
    let detalhes = [];
    
    if (entrada1 && saida1) {
        const [h1, m1] = entrada1.split(':').map(Number);
        const [h2, m2] = saida1.split(':').map(Number);
        const horas1 = (h2 * 60 + m2 - h1 * 60 - m1) / 60;
        totalHoras += horas1;
        detalhes.push(`Período 1: ${horas1.toFixed(2)}h`);
    }
    
    if (entrada2 && saida2) {
        const [h3, m3] = entrada2.split(':').map(Number);
        const [h4, m4] = saida2.split(':').map(Number);
        const horas2 = (h4 * 60 + m4 - h3 * 60 - m3) / 60;
        totalHoras += horas2;
        detalhes.push(`Período 2: ${horas2.toFixed(2)}h`);
    }
    
    if (detalhes.length === 0) {
        document.getElementById('horas-calculadas').textContent = 'Insira os horários para calcular...';
    } else {
        document.getElementById('horas-calculadas').textContent = detalhes.join(' + ') + ` = <strong>${totalHoras.toFixed(2)}h</strong>`;
    }
}

// Valida que saída é após entrada
document.getElementById('form-editar-ponto').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const entrada1 = document.querySelector('input[name="hora_entrada_1"]').value;
    const saida1 = document.querySelector('input[name="hora_saida_1"]').value;
    const entrada2 = document.querySelector('input[name="hora_entrada_2"]').value;
    const saida2 = document.querySelector('input[name="hora_saida_2"]').value;
    
    if (entrada1 && saida1 && entrada1 >= saida1) {
        alert('❌ Entrada 1 deve ser anterior a Saída 1');
        return;
    }
    
    if (entrada2 && saida2 && entrada2 >= saida2) {
        alert('❌ Entrada 2 deve ser anterior a Saída 2');
        return;
    }
    
    this.submit();
});
</script>
