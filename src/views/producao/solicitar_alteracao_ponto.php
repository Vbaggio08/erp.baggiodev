<?php
// View para solicitar alteração de ponto
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">📝 Solicitar Alteração de Ponto</h4>
                </div>
                
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Se identificou um erro em seu apontamento, utilize este formulário para solicitar a correção. 
                        Sua solicitação será revisada pelo RH.
                    </p>
                    
                    <form method="POST" id="form-solicitar-alteracao">
                        <!-- Data do Apontamento -->
                        <div class="mb-4">
                            <label class="form-label"><strong>📅 Data do Apontamento</strong></label>
                            <input type="date" name="data_apontamento" class="form-control" required>
                            <small class="form-text text-muted">Qual dia deseja corrigir?</small>
                        </div>
                        
                        <!-- Tipo de Alteração -->
                        <div class="mb-4">
                            <label class="form-label"><strong>🔧 Tipo de Alteração</strong></label>
                            <select name="tipo_alteracao" class="form-select" required onchange="atualizarCampos(this.value)">
                                <option value="">Selecione uma opção</option>
                                <option value="entrada_ausente">Entrada não foi registrada</option>
                                <option value="saida_ausente">Saída não foi registrada</option>
                                <option value="entrada_incorreta">Entrada com horário incorreto</option>
                                <option value="saida_incorreta">Saída com horário incorreto</option>
                                <option value="ambas_incorretas">Entrada e Saída incorretas</option>
                            </select>
                        </div>
                        
                        <!-- Horário Correto -->
                        <fieldset class="mb-4" id="campos-horario" style="display: none;">
                            <legend class="h5 mb-3">🕐 Horários Corretos</legend>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Entrada 1</label>
                                    <input type="time" name="entrada_1_corrigida" class="form-control">
                                    <small class="form-text text-muted">Se aplicável</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Saída 1</label>
                                    <input type="time" name="saida_1_corrigida" class="form-control">
                                    <small class="form-text text-muted">Se aplicável</small>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Entrada 2</label>
                                    <input type="time" name="entrada_2_corrigida" class="form-control">
                                    <small class="form-text text-muted">Se aplicável</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Saída 2</label>
                                    <input type="time" name="saida_2_corrigida" class="form-control">
                                    <small class="form-text text-muted">Se aplicável</small>
                                </div>
                            </div>
                        </fieldset>
                        
                        <!-- Motivo/Justificativa -->
                        <div class="mb-4">
                            <label class="form-label"><strong>📌 Motivo da Alteração</strong></label>
                            <textarea name="motivo" 
                                      class="form-control" 
                                      rows="5" 
                                      required
                                      placeholder="Descreva o motivo de sua solicitação de alteração. Seja o mais específico possível para acelerar a análise do RH..."></textarea>
                            <small class="form-text text-muted">Mínimo 20 caracteres</small>
                        </div>
                        
                        <!-- Comprovante (Opcional) -->
                        <div class="mb-4">
                            <label class="form-label">📎 Comprovante (Opcional)</label>
                            <input type="file" 
                                   name="comprovante" 
                                   class="form-control" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   maxlength="5242880">
                            <small class="form-text text-muted">Máximo 5MB. Formatos: PDF, JPG, PNG (ex: nota fiscal, recibo, foto)</small>
                        </div>
                        
                        <!-- Info sobre Processamento -->
                        <div class="alert alert-info">
                            <h6>⏱️ Prazos e Processamento</h6>
                            <ul class="mb-0 small">
                                <li>Solicitações são analisadas no prazo de <strong>48 horas</strong></li>
                                <li>Você receberá uma notificação por email sobre o resultado</li>
                                <li>Alterações aprovadas são <strong>auditadas</strong> para conformidade com CLT</li>
                            </ul>
                        </div>
                        
                        <!-- Botões -->
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                ✅ Enviar Solicitação
                            </button>
                            <a href="index.php?rota=meu_ponto" class="btn btn-secondary">
                                ❌ Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Histórico de Solicitações -->
            <div class="card shadow mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">📋 Minhas Solicitações</h5>
                </div>
                <div class="card-body">
                    <?php 
                        // $solicitacoes seria passada do controller
                        // Mostrar histórico de solicitações do usuário
                    ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Criada em</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-muted">
                                    <td colspan="4" class="text-center">Histórico será exibido aqui...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function atualizarCampos(tipo) {
    const camposHorario = document.getElementById('campos-horario');
    const inputs = camposHorario.querySelectorAll('input[type="time"]');
    
    if (tipo && tipo !== '') {
        camposHorario.style.display = 'block';
        
        // Limpar campos não usados
        inputs.forEach(input => input.value = '');
        
        // Mostrar apenas campos relevantes
        if (tipo === 'entrada_ausente' || tipo === 'entrada_incorreta') {
            document.querySelector('input[name="saida_1_corrigida"]').style.display = 'none';
            document.querySelector('input[name="saida_2_corrigida"]').style.display = 'none';
        } else if (tipo === 'saida_ausente' || tipo === 'saida_incorreta') {
            document.querySelector('input[name="entrada_1_corrigida"]').style.display = 'none';
            document.querySelector('input[name="entrada_2_corrigida"]').style.display = 'none';
        }
    } else {
        camposHorario.style.display = 'none';
    }
}

// Validação do formulário
document.getElementById('form-solicitar-alteracao').addEventListener('submit', function(e) {
    const motivo = document.querySelector('textarea[name="motivo"]').value.trim();
    
    if (motivo.length < 20) {
        e.preventDefault();
        alert('⚠️ O motivo deve ter no mínimo 20 caracteres');
    }
});
</script>
