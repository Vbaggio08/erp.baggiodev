<?php
// View para solicitação de atestado
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">📋 Solicitar Descanso por Atestado</h4>
                </div>
                
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Envie seu atestado médico para que o departamento de RH processe sua solicitação de descanso.
                        O atestado será analisado em até 24 horas.
                    </p>
                    
                    <form method="POST" id="form-solicitar-atestado" enctype="multipart/form-data">
                        <!-- Tipo de Afastamento -->
                        <div class="mb-4">
                            <label class="form-label"><strong>🏥 Tipo de Afastamento</strong></label>
                            <select name="tipo_afastamento" class="form-select" required>
                                <option value="">Selecione uma opção</option>
                                <option value="enfermidade">Enfermidade</option>
                                <option value="consulta_medica">Consulta Médica</option>
                                <option value="exame_medico">Exame Médico</option>
                                <option value="tratamento_medico">Tratamento Médico</option>
                                <option value="cirurgia">Cirurgia</option>
                                <option value="internacao">Internação</option>
                                <option value="acompanhamento_familiar">Acompanhamento Familiar</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>
                        
                        <!-- Período do Afastamento -->
                        <fieldset class="mb-4">
                            <legend class="h5 mb-3">⏰ Período de Afastamento</legend>
                            <div class="alert alert-warning">
                                <strong>⚠️ Importante:</strong> As datas devem corresponder ao período informado no atestado médico.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Data de Início</label>
                                    <input type="date" name="data_inicio" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Data de Término</label>
                                    <input type="date" name="data_fim" class="form-control" required>
                                </div>
                            </div>
                            
                            <!-- Dias Calculados -->
                            <div class="mt-3 alert alert-info">
                                <strong>📆 Total de dias:</strong> <span id="total-dias">0</span> dias
                            </div>
                        </fieldset>
                        
                        <!-- Comprovante (Atestado) -->
                        <div class="mb-4">
                            <label class="form-label"><strong>📎 Comprovante Médico</strong></label>
                            <div class="input-group">
                                <input type="file" 
                                       id="input-arquivo" 
                                       name="comprovante" 
                                       class="form-control" 
                                       accept=".pdf,.jpg,.jpeg,.png" 
                                       required
                                       onchange="atualizarNomeArquivo(this)">
                                <label class="input-group-text">Selecionar</label>
                            </div>
                            <small class="form-text text-muted">
                                Máximo 5MB. Formatos aceitos: PDF, JPG, PNG
                            </small>
                            <div id="preview-arquivo" class="mt-2"></div>
                        </div>
                        
                        <!-- Observações -->
                        <div class="mb-4">
                            <label class="form-label">📝 Observações (Opcional)</label>
                            <textarea name="observacoes" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Informações adicionais relevantes..."></textarea>
                            <small class="form-text text-muted">Ex: Necessário repouso absoluto, procedimento realizado, etc</small>
                        </div>
                        
                        <!-- Validações e Regulamentações -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6>✅ Validações Legais (CLT)</h6>
                                <ul class="small mb-0">
                                    <li><strong>Lei 8.213/1991:</strong> Atestado é comprovação válida para abono de falta</li>
                                    <li><strong>CLT Art. 476:</strong> Até 3 dias por ano dispensados sem necessidade de justificativa além do atestado</li>
                                    <li><strong>Norma Interna:</strong> Atestados são registrados em sua ficha funcional</li>
                                    <li>Atestados falsificados ou inválidos constituem motivação para rescisão contratual</li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Confirmação de Aceite -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" id="confirm-legal" name="confirm_legal" class="form-check-input" required>
                                <label class="form-check-label" for="confirm-legal">
                                    ✅ Confirmo que as informações fornecidas são verídicas e o atestado é válido
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="confirm-privacidade" name="confirm_privacidade" class="form-check-input" required>
                                <label class="form-check-label" for="confirm-privacidade">
                                    ✅ Concordo com o armazenamento e processamento de meu atestado conforme LGPD
                                </label>
                            </div>
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
            
            <!-- Histórico de Atestados -->
            <div class="card shadow mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">📊 Meus Atestados</h5>
                </div>
                <div class="card-body">
                    <?php 
                        // $meus_atestados seria passada do controller
                    ?>
                    <p class="text-muted">
                        Lista de seus atestados será exibida aqui...
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calcular dias entre as datas
document.querySelector('input[name="data_inicio"]').addEventListener('change', calcularDias);
document.querySelector('input[name="data_fim"]').addEventListener('change', calcularDias);

function calcularDias() {
    const dataInicio = document.querySelector('input[name="data_inicio"]').value;
    const dataFim = document.querySelector('input[name="data_fim"]').value;
    
    if (dataInicio && dataFim) {
        const inicio = new Date(dataInicio);
        const fim = new Date(dataFim);
        
        if (fim >= inicio) {
            const dias = Math.floor((fim - inicio) / (1000 * 60 * 60 * 24)) + 1;
            document.getElementById('total-dias').textContent = dias;
        } else {
            document.getElementById('total-dias').textContent = '0';
            alert('⚠️ Data de término não pode ser anterior a data de início');
        }
    }
}

// Preview do arquivo (opcional)
function atualizarNomeArquivo(input) {
    const preview = document.getElementById('preview-arquivo');
    const arquivo = input.files[0];
    
    if (arquivo) {
        const tamanhoMB = (arquivo.size / 1024 / 1024).toFixed(2);
        preview.innerHTML = `
            <div class="alert alert-success">
                <strong>✅ Arquivo selecionado:</strong> ${arquivo.name} (${tamanhoMB} MB)
            </div>
        `;
        
        if (arquivo.size > 5 * 1024 * 1024) {
            preview.innerHTML = `
                <div class="alert alert-danger">
                    <strong>❌ Arquivo muito grande!</strong> Máximo 5MB
                </div>
            `;
            input.value = '';
        }
    }
}

// Validar formulário
document.getElementById('form-solicitar-atestado').addEventListener('submit', function(e) {
    const dataInicio = document.querySelector('input[name="data_inicio"]').value;
    const dataFim = document.querySelector('input[name="data_fim"]').value;
    const arquivo = document.querySelector('input[name="comprovante"]').files[0];
    
    if (!arquivo) {
        e.preventDefault();
        alert('❌ Selecione o arquivo do atestado');
        return;
    }
    
    const tipoValido = ['application/pdf', 'image/jpeg', 'image/png'].includes(arquivo.type);
    if (!tipoValido) {
        e.preventDefault();
        alert('❌ Tipo de arquivo não permitido. Use PDF, JPG ou PNG');
        return;
    }
});
</script>
